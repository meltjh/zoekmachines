/* 
Richard Olij (10833730) & Melissa Tjhia (10761071)

This file makes the barcharts for the scores of the results, the number of results
per type of document and the timeline with the hits over the years.
*/

// Reformat the data to JSON object so that it can be used in displayChart
function reformatData(data) {
    json_data = [];
    // Only reformat if there are results
    if (data.length > 0) {
        for (i in data) {
            dataline = data[i];
            title = dataline[0];
            rank = parseInt(i) + 1;
            short_title = title.substring(0, 12);
            short_title = rank + ". " + short_title + "...";
            score = dataline[1];

            json_data.push({'title': short_title, score: score});
        }
        return json_data;
    }
    else {
        return null;
    }
}

// Making the chart of the data
function displayChart(dp_list,div_id){
    // Dimensions
    if (dp_list) {
        var margin = {top: 35, right: 0, bottom: 150, left: 0},
        width = 350 - margin.left - margin.right,
        height = 260 - margin.top - margin.bottom;

        var x = d3.scale.ordinal()
            .rangeRoundBands([0, width], .1);

        var y = d3.scale.linear()
            .range([height, 0]);

        var xAxis = d3.svg.axis()
            .scale(x)
            .orient("bottom");

        var yAxis = d3.svg.axis()
            .scale(y)
            .orient("left")
            .ticks(10);

        var formatValue = d3.format(".4s");
        // The tooltip when hovering on the bar (from: http://bl.ocks.org/Caged/6476579)
        var tip = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0])
            .html(function(d) {
                return "<span style='color:white'>" + formatValue(d.score) + "</span>";
      })
        
        // Add svg for the chart
        chart = d3.select(div_id).append("svg")
            .attr("class", "chart")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

            chart.call(tip);

            // Domains of the data
            x.domain(dp_list.map(function(d) { return d.title; }));    
            y.domain([0, d3.max(dp_list, function(d) { return d.score; })]);

            // x-axis and the labels
            chart.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate(0," + height + ")")
                .call(xAxis)
                    .selectAll("text")  
                    .style("text-anchor", "end")
                    .attr("dx", "-.8em")
                    .attr("dy", ".15em")
                    .attr("transform", "rotate(-70)" );
            
            // y-axis and the labels 
            chart.append("g")
                .attr("class", "y axis")
                .call(yAxis)
                .append("text")
                    .attr("transform", "rotate(-90)")
                    .attr("y", 6)
                    .attr("dy", ".71em")
                    .style("text-anchor", "end");

            // Data of the bars
            chart.selectAll(".bar")
                .data(dp_list)
                .enter().append("rect")
                    .attr("class", "bar")
                    .attr("x", function(d) { return x(d.title); })
                    .attr("y", function(d) { return y(d.score); })
                    .attr("height", function(d) { return height - y(d.score); })
                    .attr("width", x.rangeBand())
                    .on('mouseover', tip.show)
                    .on('mouseout', tip.hide);
        
            function type(d) {
                d.value = +d.value; // coerce to number
                return d;
        }
    }
}