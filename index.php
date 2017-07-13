
<!DOCTYPE html>

<html lang="en">
<head>
     <meta charset="utf-8" />
     <title>Stix 2 Graph</title>
     <style>

.node {
   stroke: #fff;
   stroke-width: 0px;
     cursor: move;
}

.link {
   stroke: #999;
   stroke-width: 1px;
   stroke-opacity: 2;
}

.label {
     fill: black;
     font-family: Verdana;
     font-size: 13px;
     text-anchor: center;
     cursor: move;
}
.label2 {
     fill: black;
     font-family: Verdana;
     font-size: 9px;
     text-anchor: center;
     cursor: move;
}


</style>
</head>
<body>

<?php

@$isCampaign1 = $_POST['campaign1'] != null;
@$isCampaign2 = $_POST['campaign2'] != null;
@$isCampaign3 = $_POST['campaign3'] != null;

?>

<form method="POST" action="">
     <h4>Select the campaigns to draw:</h4>
     <fieldset>

                 <label>
                     <input type="checkbox" id="checkbox1" name="campaign1">
                     <span id="text1"></span>
                 </label>

                 <label>
                     <input type="checkbox" id="checkbox2" name="campaign2">
                     <span id="text2"></span>
                 </label>

                 <label>
                     <input type="checkbox" id="checkbox3" name="campaign3">
                     <span id="text3"></span>
                 </label>
                 <input type="submit" id="btnDraw" value="Submit">
     </fieldset>
</form>
<script src=d3v4.js></script>
<script src=cola.min.js></script>
<script>
     var width = 1920,
         height = 1080;

     var color = d3.scaleOrdinal(d3.schemeCategory20);

     var cola = cola.d3adaptor(d3)
         .linkDistance(150)
         .avoidOverlaps(true)
         .size([width, height]);

     var svg = d3.select("body").append("svg")
         .attr("width", width)
         .attr("height", height);

     d3.json("test2.json", function (error, graph) {

         setCheckboxes(graph);


             var campaignsToRemove = [
             <?php
             if(!$isCampaign1){
                 echo "2";
             }
             if(!$isCampaign2 && !$isCampaign1){
                 echo ", 3";
             }
		     elseif(!$isCampaign2){
		         echo "3";
		     }
             if(!$isCampaign3 && (!$isCampaign1 || !$isCampaign2)){
                 echo ", 4";
             }
		     elseif(!$isCampaign3){
		         echo "4";
		     }
             ?>
             ];

             graph = filterCampaigns(graph, campaignsToRemove);



         cola
             .nodes(graph.nodes)
             .links(graph.links)
             .start();

         var link = svg.selectAll(".link")
             .data(graph.links)
           .enter().append("line")
             .attr("class", "link");

         var node = svg.selectAll(".node")
             .data(graph.nodes)
           .enter().append("rect")
             .attr("class", "node")
             .attr("width", 100)
             .attr("height", 25)
             .attr("rx", 2)
             .attr("ry", 2)
             .style("fill", function (d) {
                 return getColor(d.type);
             } )
             .call(cola.drag);



         var label = svg.selectAll(".label")
             .data(graph.nodes)
            .enter().append("text")
             .attr("class", "label")
             .text(function (d) { return d.type; })
             .call(cola.drag);

         var label2 = svg.selectAll(".label2")
             .data(graph.nodes)
             .enter().append("text")
             .attr("class", "label2")
             .text(function (d) { return d.name.substring(0, 13);  })
             .call(cola.drag);

         node.append("title")
             .text(function (d) { return "Name: "+d.name+"\n"+"Description: "+d.description; });


         cola.on("tick", function () {
             link.attr("x1", function (d) { return d.source.x; })
                 .attr("y1", function (d) { return d.source.y; })
                 .attr("x2", function (d) { return d.target.x; })
                 .attr("y2", function (d) { return d.target.y; });

             node.attr("x", function (d) { return d.x - 30 / 2; })
                 .attr("y", function (d) { return d.y - 30 / 2; });

             label.attr("x", function (d) { return d.x; })
                  .attr("y", function (d) {
                      var h = this.getBBox().height;
                      return d.y + h/4 - 8;
                  });
             label2.attr("x", function (d) { return d.x; })
                 .attr("y", function (d) {
                     var h = this.getBBox().height;
                     return d.y + h/4 + 4;
                 });

         });
     });
     function getColor(type) {
         switch (type) {
             case "malware" : return "red";
             case "campaign" : return "lavender";
             case "vulnerability" : return "sandybrown";
             case "course-of-action" : return "palegreen";
             case "attack-pattern" : return "dodgerblue";
             case "indicator" : return "yellow";
             case "identity" : return "silver";
             case "threat-actor" : return "red";
             case "report" : return "sienna";
             default : "lavender";
         }
     }

     function setCheckboxes(data) {

         var checkboxCounter = 1;
         for(var i = 0; i <data.nodes.length; i++){
             if(data.nodes[i].type == "campaign"){ document.getElementById(("text"+checkboxCounter+"")).innerHTML = data.nodes[i].name; document.getElementById(("checkbox"+checkboxCounter+"")).value = i;
                 checkboxCounter++;
             }
         }

     }

     var linksToRemove = [];
     var runCounter = 0;

     function filterCampaigns(data, nodesToRemove) {

         for(var i = 0; i < data.links.length; i++){
             for(var j = 0; j < nodesToRemove.length; j++){
                 if((data.links[i].source == nodesToRemove[j]) || (data.links[i].target == nodesToRemove[j])){
                     nodesToRemove.push(data.links[i].source);
                     nodesToRemove.push(data.links[i].target);
                     nodesToRemove = nodesToRemove.filter(function (item, i, ar) { return ar.indexOf(item) === i; });
                     linksToRemove.push(data.links[i]);
                     linksToRemove = linksToRemove.filter((function (item, i, ar) { return ar.indexOf(item) === i; }))
                 }

             }

         }
         runCounter++;
         if(runCounter < 3){
             filterCampaigns(data, nodesToRemove);
         }
         else{
             nodesToRemove.sort(function (a, b) { return a-b; });
             for(var i = nodesToRemove.length -1; i>=0; i--){
                 data.nodes.splice(nodesToRemove[i], 1);
             }
             for(var i = 0; i < linksToRemove.length; i++){
                 for(var j = 0; j < data.links.length; j++){
                     if(linksToRemove[i].id == data.links[j].id){
                         data.links.splice(j, 1);
                     }
                 }
             }
         }
         data = recalculateIndexes(data);
         return data;
     }

     function recalculateIndexes(data) {
         for(var i = 0; i < data.links.length; i++){
             var index;

             var sourceId = data.links[i].source_ref;
             index = getIndex(data, sourceId);
             data.links[i].source = index;

             var targetId = data.links[i].target_ref;
             index = getIndex(data, targetId);
             data.links[i].target = index;
         }
         return data;
     }

     function getIndex(data, id) {
         for(var i = 0; i < data.nodes.length; i++){
             if(data.nodes[i].id ==  id){
                 return i;
             }
         }
     }
</script>



</body>
</html>