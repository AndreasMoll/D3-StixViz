/**
 * Created by Andreas on 31.05.2017.
 */

var stixObjects = [], stixRelationships = [];
var dataObject;
var countNodes = 0, countLinks = 0;

function splitJSON() {
    $(document).ready(function(){
            $.getJSON('http://127.0.0.1:8080/myDB/bundle--ac946f1d-6a0e-4a9d-bc83-3f1f3bfda6ba?pagesize=200&np', function (data) {

                $(btnSplit).hide()
                console.log(data[154].type.toString());
                for(var i = 0; i < data.length; i++){

                    if(data[i].type == "relationship"){
                        stixRelationships.push(data[i]);
                        countLinks++;
                    }
                    else{
                        if(data[i].type == "report"){

                        }
                        else {
                            stixObjects.push(data[i]);
                            countNodes++;
                        }
                    }
                }
                dataObject = {nodes:stixObjects, links:stixRelationships};
                console.log("There are " +countNodes+" STIX objects and " +countLinks+ " relationships in the collection");

            })
    });


}