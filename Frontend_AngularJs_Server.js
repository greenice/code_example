//Frontend Angualr.js
    
function ArticlesProvider($http, $q, config, settings, device) {
        var url, loadData;

        url = config.serverUrl + config.articles.url;
        loadData = function (topId) {
            var defer = $q.defer(), articlesConfig;

            articlesConfig = config.articles[device.getType()];
            if (topId) {
                $http
                    .get(url.replace(':topicId', topId),
                    {timeout: config.dataProviderTimeout})
                    .then(function (r) {
                        var dataProvider = new DataProvider(
                            r.data,
                            device.isSquare() ?
                                articlesConfig.itemsPerPage / 2 :
                                articlesConfig.itemsPerPage,
                            articlesConfig.minImageSize,
                            device.isTablet(),
                            settings
                        );

                        defer.resolve(dataProvider);
                    }, function (error) {
                        defer.reject(error);
                    });
            } else {
                defer.reject({message: 'Topic id is not specified!'});
            }

            return defer.promise;
        };

        return {
            loadData: loadData
        };
    }


//Backend Node.js Emulate API given to us

var fs = require('fs');
var express = require('express');
var dummyJson = require('dummy-json');
var Chance = require('chance');
var sleep = require('sleep');


var app = express();
var chance = new Chance();
const API_PREFIX = '/rest/api/1';
const TEST_PREFIX = '/test';
const SCENARIOS_PATH = '/scenarios/';

var defaultFilesMap = {
    article: 'article.json',
    topic: 'topic.json',
    industry: 'industry.json',
    hint: 'hint.json'
};

var defaultPathMap = {
    article: __dirname + '/data/',
    topic: __dirname + '/data/',
    industry: __dirname + '/data/',
    hint: __dirname + '/data/'
};

var filesMap = {
    article: 'article.json',
    topic: 'topic.json',
    hint: 'hint.json',
    industry: 'industry.json'
};

var pathMap = {
    article: __dirname + '/data/',
    topic: __dirname + '/data/',
    hint: __dirname + '/data/',
    industry: __dirname + '/data/'
};
var scenarios, scenariosDirs = [];

app.use(TEST_PREFIX, express.static(__dirname + '/www/' + TEST_PREFIX));

app.all('*', function(req, res, next) {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'PUT, GET, POST, DELETE, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Content-Type');
    res.header('Content-Type', 'application/json');
    next();
});

app.get(API_PREFIX + '/industry', function(req, res) {
    res.send(getEntities(req.route.path.split('/').pop()));
});

app.get(API_PREFIX + '/industry/:id', function (req, res, next) {
    var indId, entity;

    indId = req.param('id');
    entity = getEntities('industry')
        .filter(function (item) {
            return indId == item.id;
        })[0];

    if (entity) {
        res.send(entity);
    } else {
        res.status(404).send('industry not found');
    }
});

app.get(API_PREFIX + '/industry/:id/topic', function(req, res) {

    sleep.sleep(1);

    var indID, entities;
    indID = req.param('id');
    entities = getEntities(req.route.path.split('/').pop())
        .filter(function (item) {
            return indID == item.indId;
    });

    entities.sort(function (a, b) {
        if (a.cat < b.cat)
            return -1;

        if (a.cat > b.cat)
            return 1;

        if (a.name < b.name ) {
            return -1;
        }

        if (a.name > b.name ) {
            return 1;
        }

        return 0;
    });
    //res.header('Content-Type', 'text/plain');
    //res.status(401).send('sssssssss');
    //res.status(402).send({error: 'bla-bla-bla', retry: false});
    res.send(entities);
});

app.get(API_PREFIX + '/topic/:id/article', function (req, res, next) {
    var topID, entities;

    sleep.sleep(4);

    topID = req.param('id');
    //res.header('Content-Type', 'text/plain');
    //res.status(401).send('sssssssss');
    //res.status(402).send({vv: 'aa'});
    if (topicExists(topID)) {
        entities = getEntities(req.route.path.split('/').pop())
            .filter(function (item) {
                return topID == item.topId;
            });
        if (entities.length) {
            res.send(entities.slice(0, 100));
        } else {
            res.send([]);
        }
    } else {
        res.status(404).send();
    }
});

app.get(API_PREFIX + '/hint', function(req, res) {
    var platform = req.param('platform');
    sleep.sleep(4);

    res.send(getEntities('hint',  platform));
});

// test module requests

app.get(TEST_PREFIX + '/scenarios', function(req, res) {
    res.send(getScenarios());
});

app.get(TEST_PREFIX + '/execute/scenario/:sId/operation/:oId', function(req, res) {
    var sId, oId, model, path;
    sId = req.param('sId');
    oId = req.param('oId');

    if (scenarios[sId].commands[oId].operation === 'load') {
        model = scenarios[sId].commands[oId].model;
        path = scenariosDirs[sId] + '/assets/';
        filesMap[model] = scenarios[sId].commands[oId].file;
        pathMap[model] = path;
    }
    res.send(getDataSet());
});

app.get(TEST_PREFIX + '/getdataset', function(req, res) {
    res.send(getDataSet());
});

app.get(TEST_PREFIX + '/setdataset', function(req, res) {
    filesMap.article = defaultFilesMap.article;
    filesMap.topic = defaultFilesMap.topic;
    pathMap.article = defaultPathMap.article;
    pathMap.topic = defaultPathMap.topic;

    filesMap.hint = defaultFilesMap.hint;
    filesMap.industry = defaultFilesMap.industry;
    pathMap.hint = defaultPathMap.hint;
    pathMap.industry = defaultPathMap.industry;
    res.send(getDataSet());
});
// test module requests end

app.get('*', function(req, res, next) {
    var err = new Error();
    err.status = 404;
    next(err);
});

// handling 404 errors
app.use(function(err, req, res, next) {
    if(err.status !== 404) {
        return next();
    }
    res.status(err.status);
    res.send(err.message || '** no unicorns here **');
});

function topicExists(id) {
    var entities = getEntities('topic')
        .filter(function (item) {
            return id == item.id;
        });
    if (entities.length) {
        return true;
    } else {
        return false;
    }
}

function getEntities(key, platform) {

    var template, templatePath, dataPath, data, filename;
    filename = filesMap[key];
    templatePath = __dirname + '/templates/' + key + '.hbs';
    dataPath = pathMap[key];

    if (!fs.existsSync(dataPath)) {
        fs.mkdir(dataPath, 0755);
    }

    if (!fs.existsSync(dataPath + filename)) {
        template = fs.readFileSync(templatePath, {encoding: 'utf8'});
        data = dummyJson.parse(template, {helpers: {
            myUniqueIndex: function(options) {
                return options.data.index + 1;
            },
            chanceText: function () {
                return chance.paragraph({sentences: 12});
            },
            chanceShortText: function () {
                return chance.paragraph({sentences: 3});
            },
            chanceTitle: function() {
                return chance.sentence({words: 8});
            }
        }});
        fs.writeFile(dataPath + filename, data);
        return JSON.parse(data);
    }
    data = fs.readFileSync(dataPath + filename, {encoding: 'utf8'});
    data = JSON.parse(data);
    if (key === 'hint' && dataPath.search('assets') !== -1) {
        data.forEach(function (item, index) {
            data[index].msg = 'Platform: ' + platform + ' ' + data[index].msg;
        });
    }
    return data;
}

function getScenarios() {
    var dirs, file, filePath;
    scenariosDirs = [];
    scenarios = [];
    dirs = fs.readdirSync(__dirname + SCENARIOS_PATH);
    dirs.forEach(function (dir) {
        filePath = __dirname + SCENARIOS_PATH + dir + '/scenario.json';
        if (fs.existsSync(filePath)) {
            file = fs.readFileSync(filePath);
            scenariosDirs.push(__dirname + SCENARIOS_PATH + dir);
            scenarios.push(JSON.parse(file));
        }
    });
    return scenarios;
}

function getDataSet() {
    var data;
    data = {
        'topics': pathMap.topic + filesMap.topic,
        'articles': pathMap.article + filesMap.article,
        'hints': pathMap.hint + filesMap.hint,
        'industry': pathMap.industry + filesMap.industry
    };
    return data;
}

app.listen(8888);