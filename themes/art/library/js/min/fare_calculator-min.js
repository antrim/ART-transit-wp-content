function load_data(e,n,t){n="undefined"!=typeof n?n:"json",t="undefined"!=typeof t?t:!1;var a=null;return $.ajax({async:t,global:!1,url:e,dataType:n,success:function(e){a=e}}),a}function setup_fare_zones(e,n){if(n="undefined"!=typeof n?n:null,null!==n)var t="&origin_id="+n;else var t="";zones=load_data("http://trilliumtransit.com/applications/fare_calculator/json_zones.php?agency_id="+agency_id+t);for(var a=new Array,o=0;o<zones.length;o++){var s=zones[o].zone_id,l=zones[o].zone_name;a.push("<option value='"+s+"'>"+l+"</option>"),console.log("<option value='"+s+"'>"+l+"</option>")}a=a.join(""),e.empty(),e.append(a),console.log("these are the optionsHtml: ".optionsHtml)}function update_fare_divs(e){console.log("amounts: "+e),$("#regular_fare").html(e.regular)}function clear_fare_result(){console.log("Clearing fare zone.");var e={regular:"$ --"};update_fare_divs(e)}var all_zones,select_start_zone=$("#start_zone"),select_end_zone=$("#end_zone"),agency_id=194;setup_fare_zones(select_start_zone),select_start_zone.change(function(){setup_fare_zones(select_end_zone,select_start_zone.val())}),$("#fare_zones").submit(function(){return console.log($(this).attr("action")),console.log($(this).serialize()),$.ajax({data:$(this).serialize(),type:$(this).attr("method"),url:$(this).attr("action"),success:function(e){update_fare_divs(e),$("#get-fares-results").addClass("fare-calculated")}}),!1}),select_start_zone.onchange=clear_fare_result(),select_end_zone.onchange=clear_fare_result();