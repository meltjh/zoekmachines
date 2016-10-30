/* 
Richard Olij (10833730) & Melissa Tjhia (10761071)

This file makes the word cloud, based on the most frequent words in the top results.

Mostly from http://bl.ocks.org/ericcoopey/6382449
*/


// Our colors
var color = d3.scale.linear()
    .domain([2,3,5,9,20000000])
    .range(["#41b6b8",
"#41b6c4",
"#1d91c0",
"#225ea8",
"#0c2c84"]);

function initializeCould(frequency_list){
    d3.layout.cloud().size([180, 230]) // Size where the words will be placed (note that words can grow out of this size)
        .words(frequency_list)
        .rotate(0)
        .fontSize(function(d) { return d.size/2; })
        .on("end", draw)
        .start();
}

function draw(words) {
    d3.select('#dtopright').append("svg")
        .attr("width", 350) // size of the object
        .attr("height", 260)
        .attr("class", "wordcloud")
        .append("g")
        // Without the transform, words words would get cutoff to the left and top, they would appear outside of the SVG area
        .attr("transform", "translate(80,120)")
        .selectAll("text")
        .data(words)
        .enter().append("text")
        .style("font-size", function(d) {
                                        // Maximum font size
                                        if (d.size > 40) {
                                            return "40px";
                                        }
                                        // Minimum font size
                                        else if (d.size < 10) {
                                            return "8px";
                                        }
                                        else {
                                            return d.size*0.85 + "px"; 
                                         }})
        .style("fill", function(d, i) { return color(i); })
        .attr("transform", function(d) {
            return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
        })
        .text(function(d) { return d.text; });
}