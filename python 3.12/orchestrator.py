from .core.models import VectorDefinition, FormulaNode, UnitConversion
from .core.enums import DataGranularity, UnitType, ParameterType
from .logic.sql_factory import ContinuousAggregateFactory
from .logic.vector_composer import VirtualVectorComposer

class EnergyIntelligenceSDK:
    """
    Public API for the Energy Vector Intelligence system.
    Orchestrates high-level TimescaleDB and Vector logic for energy data management.
    """

    def __init__(self):
        self.sql_factory = ContinuousAggregateFactory()
        self.composer = VirtualVectorComposer()

    def deploy_neural_training_dataset(self, site_id: str):
        print(f"--- INITIALIZING DEPLOYMENT FOR SITE: {site_id} ---")
        
        # 1. Define Sensors (Metadata extracted from 'Business Logic')
        main_meter = VectorDefinition(
            id="METER-001", name="Main Grid Meter", 
            parameter_type=ParameterType.ACTIVE_POWER.value,
            unit=UnitType.WATT, granularity=DataGranularity.MIN_1
        )
        
        solar_inverter = VectorDefinition(
            id="SOLAR-X2", name="PV Generation", 
            parameter_type=ParameterType.ACTIVE_POWER.value,
            unit=UnitType.WATT, granularity=DataGranularity.MIN_1
        )

        # 2. Generate Physical Layer (Continuous Aggregates)
        print("\n[STEP 1] Generating Physical Aggregator SQL:")
        print(self.sql_factory.generate_create_sql(main_meter))
        print(self.sql_factory.generate_create_sql(solar_inverter))

        # 3. Define Vector Arithmetic (e.g. Total Consumption = Grid + Solar)
        # Integration of "Self-Consumption" logic for enhanced data modeling
        training_formula = FormulaNode(
            operator="+",
            operands=["METER-001", "SOLAR-X2"]
        )

        conversions = {
            "METER-001": UnitConversion(factor=0.001, target_unit=UnitType.KILO_WATT),
            "SOLAR-X2": UnitConversion(factor=0.001, target_unit=UnitType.KILO_WATT)
        }

        # 4. Compose the Dataset View
        print("\n[STEP 2] Composing Dataset for Neural Model (Training Set):")
        dataset_sql = self.composer.compose_calculated_vector(
            output_name=f"site_{site_id}_total_load_kw",
            root_formula=training_formula,
            input_vectors=[main_meter, solar_inverter],
            conversions=conversions,
            target_granularity=900 # Resample to 15min for training stability
        )
        print(dataset_sql)

if __name__ == "__main__":
    sdk = EnergyIntelligenceSDK()
    sdk.deploy_neural_training_dataset("EUROPE-WEST-14")
