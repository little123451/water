define(function(require) {
    Ext.chart.Chart.CHART_URL = './core/charts.swf';

    var mp = require('./frame/MainPanel.js');

    new mp.MainPanel();

});

