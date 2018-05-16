window.addEventListener("DOMContentLoaded", function () {
    var dim = {
        width: document.getElementById('techan_js').offsetWidth - 5,
        height: document.getElementById('techan_js').offsetHeight - 5,
        margin: {
            top: 20,
            right: 50,
            bottom: 30,
            left: 50
        },
        ohlc: {
            height: document.getElementById('techan_js').offsetHeight - 55
        },
        indicator: {
            height: 15,
            padding: 5
        }
    };
    dim.plot = {
        width: dim.width - dim.margin.left - dim.margin.right,
        height: dim.height - dim.margin.top - dim.margin.bottom
    };

    var parseDate = d3.timeParse("%s");

    var zoom = d3.zoom()
    .on("zoom", zoomed);

    var x = techan.scale.financetime()
    .range([0, dim.plot.width]);

    var y = d3.scaleLinear()
    .range([dim.ohlc.height, 0]);


    var yPercent = y.copy();   // Same as y at this stage, will get a different domain later

    var yInit, yPercentInit, zoomableInit;

    var yVolume = d3.scaleLinear()
    .range([y(0), y(0.2)]);

    var candlestick = techan.plot.candlestick()
    .xScale(x)
    .yScale(y);

    var tradearrow = techan.plot.tradearrow()
    .xScale(x)
    .yScale(y)
    .y(function(d) {
        // Display the buy and sell arrows a bit above and below the price, so the price is still visible
        if(d.type === 'buy') return y(d.low)+5;
        if(d.type === 'sell') return y(d.high)-5;
        else return y(d.price);
    });

    var sma0 = techan.plot.sma()
    .xScale(x)
    .yScale(y);

    var sma1 = techan.plot.sma()
    .xScale(x)
    .yScale(y);

    var ema2 = techan.plot.ema()
    .xScale(x)
    .yScale(y);

    var volume = techan.plot.volume()
    .accessor(candlestick.accessor())   // Set the accessor to a ohlc accessor so we get highlighted bars
    .xScale(x)
    .yScale(yVolume);

    var trendline = techan.plot.trendline()
    .xScale(x)
    .yScale(y);

    var supstance = techan.plot.supstance()
    .xScale(x)
    .yScale(y);

    var xAxis = d3.axisBottom(x);

    var timeAnnotation = techan.plot.axisannotation()
    .axis(xAxis)
    .orient('bottom')
    .format(d3.timeFormat('%Y-%m-%d'))
    .width(65)
    .translate([0, dim.plot.height]);

    var yAxis = d3.axisRight(y);

    var ohlcAnnotation = techan.plot.axisannotation()
    .axis(yAxis)
    .orient('right')
    .format(d3.format(',.2f'))
    .translate([x(1), 0]);

    var closeAnnotation = techan.plot.axisannotation()
    .axis(yAxis)
    .orient('right')
    .accessor(candlestick.accessor())
    .format(d3.format(',.2f'))
    .translate([x(1), 0]);

    var percentAxis = d3.axisLeft(yPercent)
    .tickFormat(d3.format('+.1%'));

    var percentAnnotation = techan.plot.axisannotation()
    .axis(percentAxis)
    .orient('left');

    var volumeAxis = d3.axisRight(yVolume)
    .ticks(3)
    .tickFormat(d3.format(",.3s"));

    var volumeAnnotation = techan.plot.axisannotation()
    .axis(volumeAxis)
    .orient("right")
    .width(35);

    var ohlcCrosshair = techan.plot.crosshair()
    .xScale(timeAnnotation.axis().scale())
    .yScale(ohlcAnnotation.axis().scale())
    .xAnnotation(timeAnnotation)
    .yAnnotation([ohlcAnnotation, percentAnnotation, volumeAnnotation])
    .verticalWireRange([0, dim.plot.height]);

    var svg = d3.select("#techan_js").append("svg")
    .attr("width", dim.width)
    .attr("height", dim.height);

    var defs = svg.append("defs");

    defs.append("clipPath")
    .attr("id", "ohlcClip")
    .append("rect")
    .attr("x", 0)
    .attr("y", 0)
    .attr("width", dim.plot.width)
    .attr("height", dim.ohlc.height);

    svg = svg.append("g")
    .attr("transform", "translate(" + dim.margin.left + "," + dim.margin.top + ")");

    svg.append('text')
    .attr("class", "symbol")
    .attr("x", 20)
    .text(document.getElementById('techan_js').dataset.symbol);

    svg.append("g")
    .attr("class", "x axis")
    .attr("transform", "translate(0," + dim.plot.height + ")");

    var ohlcSelection = svg.append("g")
    .attr("class", "ohlc")
    .attr("transform", "translate(0,0)");

    ohlcSelection.append("g")
    .attr("class", "axis")
    .attr("transform", "translate(" + x(1) + ",0)")
    .append("text")
    .attr("transform", "rotate(-90)")
    .attr("y", -12)
    .attr("dy", ".71em")
    .style("text-anchor", "end")
    .text("Price (" + document.getElementById('techan_js').dataset.index + ")");

    ohlcSelection.append("g")
    .attr("class", "close annotation up");

    ohlcSelection.append("g")
    .attr("class", "volume")
    .attr("clip-path", "url(#ohlcClip)");

    ohlcSelection.append("g")
    .attr("class", "candlestick")
    .attr("clip-path", "url(#ohlcClip)");

    ohlcSelection.append("g")
    .attr("class", "indicator sma ma-0")
    .attr("clip-path", "url(#ohlcClip)");

    ohlcSelection.append("g")
    .attr("class", "indicator sma ma-1")
    .attr("clip-path", "url(#ohlcClip)");

    ohlcSelection.append("g")
    .attr("class", "indicator ema ma-2")
    .attr("clip-path", "url(#ohlcClip)");

    ohlcSelection.append("g")
    .attr("class", "percent axis");

    ohlcSelection.append("g")
    .attr("class", "volume axis");

    var indicatorSelection = svg.selectAll("svg > g.indicator").data(["macd", "rsi"]).enter()
    .append("g")
    .attr("class", function(d) { return d + " indicator"; });

    indicatorSelection.append("g")
    .attr("class", "axis right")
    .attr("transform", "translate(" + x(1) + ",0)");

    indicatorSelection.append("g")
    .attr("class", "axis left")
    .attr("transform", "translate(" + x(0) + ",0)");

    indicatorSelection.append("g")
    .attr("class", "indicator-plot")
    .attr("clip-path", function(d, i) { return "url(#indicatorClip-" + i + ")"; });

    // Add trendlines and other interactions last to be above zoom pane
    svg.append('g')
    .attr("class", "crosshair ohlc");

    svg.append("g")
    .attr("class", "tradearrow")
    .attr("clip-path", "url(#ohlcClip)");

    svg.append('g')
    .attr("class", "crosshair macd");

    svg.append('g')
    .attr("class", "crosshair rsi");

    svg.append("g")
    .attr("class", "trendlines analysis")
    .attr("clip-path", "url(#ohlcClip)");
    svg.append("g")
    .attr("class", "supstances analysis")
    .attr("clip-path", "url(#ohlcClip)");

    d3.select("button").on("click", reset);

    d3.csv("assets/scripts/chart.php?pair=" + document.getElementById('techan_js').dataset.pair, function(error, data) {
        var accessor = candlestick.accessor(),
        indicatorPreRoll = 33;  // Don't show where indicators don't have data

        data = data.map(function(d) {
            return {
                date: parseDate(d.Date),
                open: +d.Open,
                high: +d.High,
                low: +d.Low,
                close: +d.Close,
                volume: +d.Volume
            };
        }).sort(function(a, b) { return d3.ascending(accessor.d(a), accessor.d(b)); });

        x.domain(techan.scale.plot.time(data).domain());
        y.domain(techan.scale.plot.ohlc(data.slice(indicatorPreRoll)).domain());
        yPercent.domain(techan.scale.plot.percent(y, accessor(data[indicatorPreRoll])).domain());
        yVolume.domain(techan.scale.plot.volume(data).domain());

        var trendlineData = [
            { start: { date: new Date(2014, 2, 11), value: 72.50 }, end: { date: new Date(2014, 5, 9), value: 63.34 } },
            { start: { date: new Date(2013, 10, 21), value: 43 }, end: { date: new Date(2014, 2, 17), value: 70.50 } }
        ];

        var supstanceData = [
            { start: new Date(2014, 2, 11), end: new Date(2014, 5, 9), value: 63.64 },
            { start: new Date(2013, 10, 21), end: new Date(2014, 2, 17), value: 55.50 }
        ];

        var trades = [
            { date: data[67].date, type: "buy", price: data[67].low, low: data[67].low, high: data[67].high },
            { date: data[100].date, type: "sell", price: data[100].high, low: data[100].low, high: data[100].high },
            { date: data[130].date, type: "buy", price: data[130].low, low: data[130].low, high: data[130].high },
            { date: data[170].date, type: "sell", price: data[170].low, low: data[170].low, high: data[170].high }
        ];

        svg.select("g.candlestick").datum(data).call(candlestick);
        svg.select("g.close.annotation").datum([data[data.length-1]]).call(closeAnnotation);
        svg.select("g.volume").datum(data).call(volume);
        svg.select("g.sma.ma-0").datum(techan.indicator.sma().period(10)(data)).call(sma0);
        svg.select("g.sma.ma-1").datum(techan.indicator.sma().period(20)(data)).call(sma1);
        svg.select("g.ema.ma-2").datum(techan.indicator.ema().period(50)(data)).call(ema2);

        svg.select("g.crosshair.ohlc").call(ohlcCrosshair).call(zoom);
        svg.select("g.trendlines").datum(trendlineData).call(trendline).call(trendline.drag);
        svg.select("g.supstances").datum(supstanceData).call(supstance).call(supstance.drag);

        svg.select("g.tradearrow").datum(trades).call(tradearrow);

        // Stash for zooming
        zoomableInit = x.zoomable().domain([indicatorPreRoll, data.length]).copy(); // Zoom in a little to hide indicator preroll
        yInit = y.copy();
        yPercentInit = yPercent.copy();

        draw();
    });

    function reset() {
        zoom.scale(1);
        zoom.translate([0,0]);
        draw();
    }

    function zoomed() {
        x.zoomable().domain(d3.event.transform.rescaleX(zoomableInit).domain());
        y.domain(d3.event.transform.rescaleY(yInit).domain());
        yPercent.domain(d3.event.transform.rescaleY(yPercentInit).domain());

        draw();
    }

    function draw() {
        svg.select("g.x.axis").call(xAxis);
        svg.select("g.ohlc .axis").call(yAxis);
        svg.select("g.volume.axis").call(volumeAxis);
        svg.select("g.percent.axis").call(percentAxis);

        // We know the data does not change, a simple refresh that does not perform data joins will suffice.
        svg.select("g.candlestick").call(candlestick.refresh);
        svg.select("g.close.annotation").call(closeAnnotation.refresh);
        svg.select("g.volume").call(volume.refresh);
        svg.select("g .sma.ma-0").call(sma0.refresh);
        svg.select("g .sma.ma-1").call(sma1.refresh);
        svg.select("g .ema.ma-2").call(ema2.refresh);
        svg.select("g.crosshair.ohlc").call(ohlcCrosshair.refresh);
        svg.select("g.trendlines").call(trendline.refresh);
        svg.select("g.supstances").call(supstance.refresh);
        svg.select("g.tradearrow").call(tradearrow.refresh);
    }
});