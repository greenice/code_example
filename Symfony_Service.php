<?php


namespace AppBundle\Service;


use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\LockMode;
use kandelyabre\Emoney\ServiceBundle\Service\AdditionalServiceInterface;
use kandelyabre\Emoney\ServiceBundle\Service\Egopay\Egopay;
use kandelyabre\Emoney\ServiceBundle\Service\Lavapay\Lavapay;
use kandelyabre\Emoney\ServiceBundle\Service\Lavapay\LavapayBase;
use kandelyabre\Emoney\ServiceBundle\Service\Perfectmoney\Perfectmoney;
use kandelyabre\Emoney\ServiceBundle\Service\Webmoney\Webmoney;
use kandelyabre\Symfony\Component\DependencyInjection\Traits\ContainerTrait;
use kandelyabre\Symfony\Component\DependencyInjection\Traits\EntityManagerTrait;
use kandelyabre\Symfony\Component\DependencyInjection\Traits\EventDispatcherTrait;
use kandelyabre\Symfony\Component\DependencyInjection\Traits\TranslatorTrait;
use kandelyabre\Emoney\ServiceBundle\Service\BalanceInterface;
use kandelyabre\Emoney\ServiceBundle\Exception\Exception;
use kandelyabre\Emoney\ServiceBundle\Service\EmoneyInterface;
use kandelyabre\Emoney\ServiceBundle\Service\TransactionOutInterface;
use kandelyabre\Emoney\ServiceBundle\Service\Okpay\Okpay;
use kandelyabre\Emoney\ServiceBundle\Service\Paxum\Paxum;
use kandelyabre\Emoney\ServiceBundle\Service\Privat24\Privat24Base;
use kandelyabre\Emoney\ServiceBundle\Service\Qiwi\Qiwi;
use kandelyabre\Emoney\ServiceBundle\Service\Yandex\Yandex;
use kandelyabre\Emoney\ServiceBundle\Transaction\Transaction;
use kandelyabre\Emoney\ServiceBundle\Transaction\TransactionStatus;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderTransaction;
use AppBundle\Entity\Service;
use AppBundle\Order\Event\OrderStatusChangeEvent;
use AppBundle\Order\OrderStatus;
use AppBundle\Order\Transaction\Event\OrderTransactionEvent;
use AppBundle\Order\Transaction\TransactionType;
use itdv\UserBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EMoneyExchange
 *
 */
class EMoneyExchange
{
    use ContainerTrait, EntityManagerTrait, TranslatorTrait, EventDispatcherTrait;

    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Service $entity
     *
     * @return \kandelyabre\Emoney\ServiceBundle\Service\EmoneyInterface
     * @throws EMoneyExchangeException
     */
    final public function getEMoneyService(Service $entity = null)
    {
        if (!$entity) {
            throw new EMoneyExchangeException(EMoneyExchangeException::SERVICE_NOT_FOUND);
        }

        return $this->getContainer()->get($entity->getName());
    }

    /**
     * @param int $serviceId
     *
     * @return EmoneyInterface
     */
    final public function getEMoneyServiceById($serviceId)
    {
        return $this->getEMoneyService(
            $this->getEntityManager()->getRepository('AppBundle:Service')->find($serviceId)
        );
    }

    /**
     * @param string $serviceName
     *
     * @return EmoneyInterface
     */
    final public function getEMoneyServiceByName($serviceName)
    {
        return $this->getEMoneyService(
            $this->getEntityManager()->getRepository('AppBundle:Service')->findOneByName($serviceName)
        );
    }

    /**
     * обновление баланса на счетах
     * @param int $timeLimit
     * @param array $ids
     *
     * @return Service[]
     */
    final public function updateBalance($timeLimit = 60, array $ids = null)
    {
        $rc = [];
        foreach ($this->getEntityManager()->getRepository('AppBundle:Service')->findBy($query) as $entity) {

            $service = $this->getEMoneyService($entity);
            if ($service instanceof BalanceInterface && time() - $entity->getupdatedAt()->format('U') > $timeLimit) {
                try {
                    $balance = $service->getBalance();
                    if (!is_null($balance)) {
                        if ($balance != $entity->getBalance()) {
                            $entity->setBalance($balance);
                            $this->container->get('logger')->info(
                                "Balance of {$entity->getName()} updated: {$balance}"
                            );
                        }
                        $rc[] = $entity;
                        $this->getEntityManager()->flush($entity);
                    }
                } catch (\Exception $exception) {
                    $this->container->get('logger')->error($exception->getMessage());
                }
            }
        }

        return $rc;
    }

    /**
     * @param Order $orderEntity
     *
     * @return Order
     * @throws DBALException
     * @throws \Exception
     */
    public function createOrder(Order $orderEntity)
    {
        if (!$orderEntity->getServiceIn()->getActive() || !$orderEntity->getServiceOut()->getActive()) {
            throw new Exception(EMoneyExchangeException::SERVICE_NOT_FOUND);
        }

        $orderEntity->setId($this->getEntityManager()->getRepository('AppBundle:Order')->generateId());

        $this->getEntityManager()->persist($orderEntity);


        $this->getEventDispatcher()->dispatch(
            'itdv.exchange.order.status.change',
            new OrderStatusChangeEvent($orderEntity)
        );

        return $orderEntity;
    }

    /**
     * подпись для id заявки
     * @param string $orderId
     *
     * @return string
     */
    public function getUriOrderSign($orderId)
    {
        return md5(sprintf('%s:%s', $orderId, $this->getContainer()->getParameter('secret')));
    }

    /**
     * получение модели заявки
     * @param int $orderId
     * @param int $lockMode
     * @param int $lockVersion
     *
     * @return Order
     */
    public function getOrderEntity($orderId, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        return $this->getEntityManager()->find('AppBundle:Order', $orderId, $lockMode, $lockVersion);
    }

    /**
     * получение сервиса для входящей транзакции
     * @param int $orderId
     *
     * @return EmoneyInterface
     */
    public function getEMoneyServiceIn($orderId)
    {
        return $this->getEMoneyServiceById($this->getOrderEntity($orderId)->getServiceIn()->getId());
    }

    /**
     * @param int $orderId
     *
     * @return EmoneyInterface
     */
    public function getEMoneyServiceOut($orderId)
    {
        return $this->getEMoneyServiceById($this->getOrderEntity($orderId)->getServiceOut()->getId());
    }

    /**
     * @param OrderTransaction $transactionEntity
     * @param Transaction $eMoneyTransactionInfo
     *
     * @return OrderTransaction
     */
    private function fillTransactionEntity(OrderTransaction $transactionEntity, Transaction $eMoneyTransactionInfo)
    {
        $transactionEntity->setAccountFrom($eMoneyTransactionInfo->getAccountFrom());
        $transactionEntity->setAccountTo($eMoneyTransactionInfo->getAccountTo());
        $transactionEntity->setAmount(floatval($eMoneyTransactionInfo->getAmount()));
        $transactionEntity->setStatus($eMoneyTransactionInfo->getStatus());

        $transactionEntity->setRemoteId($eMoneyTransactionInfo->getRemoteTransactionId());
        $transactionEntity->setDescription($eMoneyTransactionInfo->getDescription());

        return $transactionEntity;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->getContainer()->get('monolog.logger.transaction');
    }

    /**
     * @param OrderTransactionEvent $event
     */
    public function onTransactionIn(OrderTransactionEvent $event)
    {

        $transactionInfo = $event->getTransactionInfo();
        $this->getEntityManager()->beginTransaction();

        $orderEntity = $this->getOrderEntity($transactionInfo->getTransactionId(), LockMode::PESSIMISTIC_WRITE);
        $event = new OrderStatusChangeEvent($orderEntity, $orderEntity->getStatus());

        $transactionEntity = $this->fillTransactionEntity($orderEntity->getNewTransaction(), $transactionInfo);
        $transactionEntity->setService($orderEntity->getServiceIn());
        $transactionEntity->setType(TransactionType::IN);

        $info = [];
        if (!$orderEntity->getServiceIn()->getActive()) {
            $this->getLogger()->emergency(
                "ServiceIn is not active!",
                ['order' => $orderEntity->getId(), 'service' => $orderEntity->getServiceIn()->getName()]
            );
            throw new Exception(Exception::PAYMENT_REQUEST_COMMON_ERROR);
        }
        if ($this->getEMoneyServiceIn($orderEntity->getId())->getServiceName()
            !== $transactionInfo->getService()->getServiceName()
        ) {
            $this->getLogger()->emergency(
                "Different service!",
                ['order' => $orderEntity->getId(), 'serviceFromOrder' => $orderEntity->getServiceIn()->getName(), 'serviceFromTransaction'=>$transactionInfo->getService()->getServiceName()]
            );
            throw new Exception(Exception::PAYMENT_REQUEST_COMMON_ERROR);

        }
        if ($this->checkOrderStatus($orderEntity, [OrderStatus::IN, OrderStatus::IN_CHECK], false)) {

            if ($transactionInfo->getStatus() == TransactionStatus::SUCCESS) {

                if ($orderEntity->getAmount() > $transactionInfo->getAmount()) {
                    $orderEntity->setStatus(OrderStatus::IN_FAIL);

                } else {
                    $orderEntity->setStatus(OrderStatus::OUT);

                    $serviceIn = $this->getEMoneyService($orderEntity->getServiceIn());
                    $serviceOut = $this->getEMoneyService($orderEntity->getServiceOut());
                }
            } elseif ($transactionInfo->getStatus() == TransactionStatus::FAIL) {
                $orderEntity->setStatus(OrderStatus::IN_FAIL);
            }
        } 

        $info[] = $transactionEntity->getDescription();

        $transactionEntity->setDescription(implode(PHP_EOL, array_filter($info)));

        $this->getEntityManager()->persist($transactionEntity);
        $this->getEntityManager()->persist($orderEntity);
        $this->getEntityManager()->flush();

        $this->getEntityManager()->commit();

        if ($event->needDispatch()) {
            $this->getEventDispatcher()->dispatch('itdv.exchange.order.status.change', $event);
        }

    }


    /**
     * @param OrderTransactionEvent $event
     *
     * @return bool
     */
    public function onTransactionOut(OrderTransactionEvent $event)
    {
        $transactionEntity = $this->fillTransactionEntity($event->getTransactionEntity(), $event->getTransactionInfo());

        $this->getEntityManager()->persist($transactionEntity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Service $service
     */
    private function _updateBalance(Service $service)
    {
        $emoneyService = $this->getEMoneyService($service);
        if ($emoneyService instanceof LavapayBase) {
            $services = $this->getEntityManager()->getRepository('AppBundle:Service')->findLavapayServicesByCurrency(
                $emoneyService->getCurrency()
            );
            $ids = [];
            foreach ($services as $service) {
                $ids[] = $service->getId();
            }
            if ($ids) {
                $this->updateBalance(0, $ids);
            }
        } else {
            $this->updateBalance(0, [$service->getId()]);
        }
    }

    /**
     * @param OrderStatusChangeEvent $event
     */
    public function onOrderStatusChange(OrderStatusChangeEvent $event)
    {
        $order = $event->getEntity();
        switch ($order->getStatus()) {
            case OrderStatus::OUT:
                if ($this->getEMoneyServiceOut($order->getId()) instanceof TransactionOutInterface) {
                    $this->transactionOut($order->getId());
                    $this->_updateBalance($order->getServiceIn());
                }
                break;
            case OrderStatus::DONE:
                $this->_updateBalance($order->getServiceOut());
                break;
        }
    }


    /**
     * @param int $orderId
     * @param int $orderStatus
     * @param User $user
     *
     * @throws \Exception
     * @throws EMoneyExchangeException
     */
    public function transactionOut($orderId, $orderStatus = OrderStatus::OUT, User $user = null)
    {
        $orderEntity = $this->changeOrderStatus($orderId, $orderStatus, OrderStatus::OUT_START);
        if (!$orderEntity->getServiceIn()->getActive()) {
            $this->getLogger()->emergency(
                "ServiceOut is not active!",
                ['order' => $orderEntity->getId(), 'service' => $orderEntity->getServiceOut()->getName()]
            );
            throw new Exception(Exception::PAYMENT_REQUEST_COMMON_ERROR);
        }
        $transactionEntity = $orderEntity->getNewTransaction();
        $transactionEntity->setType(TransactionType::OUT);
        $transactionEntity->setAccountTo($orderEntity->getInfoByType(EMoneyExchangeInfoType::TRANSACTION_ACCOUNT_TO));
        $transactionEntity->setAmount($orderEntity->getTransferAmount());
        $transactionEntity->setService($orderEntity->getServiceOut());

        $this->getEntityManager()->persist($transactionEntity);
        $this->getEntityManager()->flush();

        $this->getEntityManager()->beginTransaction();

        $orderEntity = $this->getOrderEntity($orderId, LockMode::PESSIMISTIC_WRITE);
        try {
            $this->checkOrderStatus(
                $orderEntity,
                OrderStatus::OUT_START,
                new EMoneyExchangeException(EMoneyExchangeException::TRANSACTION_STATUS_ERROR)
            );
        } catch (EMoneyExchangeException $exception) {
            $this->getEntityManager()->commit();
            throw $exception;
        }

        $event = new OrderStatusChangeEvent($orderEntity, $orderEntity->getStatus());

        $serviceOut = $this->getEMoneyService($this->getOrderEntity($orderId)->getServiceOut());

        if ($serviceOut instanceof TransactionOutInterface) {
            $transactionInfo = $serviceOut->transferTo(
                $orderEntity->getInfoByType(EMoneyExchangeInfoType::TRANSACTION_ACCOUNT_TO),
                $orderEntity->getTransferAmount(),
                $transactionEntity->getId(),
                $this->getCommentByOrder($orderEntity, $serviceOut)
            );
        }

        if (isset($transactionInfo)) {

            if ($user) {
                $transactionInfo->setDescription(
                    implode(
                        PHP_EOL,
                        array_filter(
                            [
                                sprintf('Оператор %s [%d]', $user->getEmail(), $user->getId()),
                                $transactionInfo->getDescription(),
                            ]
                        )
                    )
                );
            }

            switch ($transactionInfo->getStatus()) {
                case TransactionStatus::SUCCESS:
                    $orderEntity->setStatus(OrderStatus::DONE);
                    break;
                case TransactionStatus::IN_PROCESS:
                    $orderEntity->setStatus(OrderStatus::OUT_CHECK);
                    break;
                case TransactionStatus::FAIL:
                    $orderEntity->setStatus(OrderStatus::OUT_FAIL);
                    break;
            }

            $this->getEventDispatcher()->dispatch(
                'itdv.exchange.order.transaction.out',
                new OrderTransactionEvent($transactionInfo, $transactionEntity)
            );
        }

        $this->getEntityManager()->persist($orderEntity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->commit();

        if ($event->needDispatch()) {
            $this->getEventDispatcher()->dispatch('itdv.exchange.order.status.change', $event);

        }
    }

    /**
     * @param Order $orderEntity
     * @param int|array $status
     * @param \Exception|bool|null $exception
     *
     * @return bool
     * @throws \Exception
     * @throws EMoneyExchangeException
     */
    public function checkOrderStatus(Order $orderEntity, $status, $exception = null)
    {
        $rc = true;

        if (!is_array($status)) {
            $status = array($status);
        }

        if (!in_array($orderEntity->getStatus(), $status)) {
            $rc = false;

            if ($exception !== false) {
                if (!$exception instanceof \Exception) {
                    $exception = new EMoneyExchangeException('Статус заявки не соответствует');
                }
                throw $exception;
            }
        }

        return $rc;
    }

    /**
     * @param int $orderId
     * @param int|array $fromStatus
     * @param int $toStatus
     * @param \Exception $exception
     *
     * @return Order
     * @throws \Exception
     * @throws EMoneyExchangeException
     */
    public function changeOrderStatus($orderId, $fromStatus, $toStatus, \Exception $exception = null)
    {
        OrderStatus::validate($toStatus);

        $this->getEntityManager()->beginTransaction();

        $orderEntity = $this->getOrderEntity($orderId, LockMode::PESSIMISTIC_WRITE);
        $event = new OrderStatusChangeEvent($orderEntity, $orderEntity->getStatus());

        try {
            $this->checkOrderStatus(
                $orderEntity,
                $fromStatus,
                $exception ? $exception : new EMoneyExchangeException('Невозможно изменить статус заявки')
            );

            $orderEntity->setStatus($toStatus);
            $this->getEntityManager()->persist($orderEntity);
            $this->getEntityManager()->flush();
        } catch (\Exception $exception) {
            //
        }

        $this->getEntityManager()->commit();

        if ($event->needDispatch()) {
            $this->getEventDispatcher()->dispatch('itdv.exchange.order.status.change', $event);
        }

        return $orderEntity;
    }

}