<?php
// include function files for this application
//Energy Requirements for Growth in the Norfolk Terrier
require_once('bookmark_fns.php');
session_start();
?>


<script>
	  if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
        var url = 'http://localhost/sandbox/wp-content/apps/users/ajax_db.php';
      }
	  else{
		var url = 'https://petcalculator.com/wp-content/apps/users/ajax_db.php';
	  }
    

var dog_id_param = -1;
var dog_weight_unit = 'lbs'  // use lbs default, better may wil be to customize for each user
const pounds_to_kg = 0.453592;
const kg_to_pounds = 1.0/pounds_to_kg;

function get_default_dog_id(){
  $.ajax({ url: url,
         data: {action: "get_default_dog_id",'valid_user':valid_user},
         type: 'post',
         success: function(output,status,jqXHR) {
           console.log('the default id retreived: '.concat(output))
          
          return output; 
          
            
          }
    });  
}

function update_dashboard(){
  
      $.ajax({ url: url,
      data: {action: "display_dog_weights","valid_user":valid_user,"dog_id_param":dog_id_param,"dog_weight_unit":dog_weight_unit},
      type: 'post',
      success: function(output,status,jqXHR) {

        dashboard = document.getElementById('dashboard-div')

        output = jQuery.parseJSON(output);
        

        if (dog_weight_unit == 'lbs'){
          weights = output.weights;
        }
      
        if (dog_weight_unit == 'kg'){

          for (let index = 0; index < output.weights.length; index++) {
            weights[index] = (output.weights[index]*pounds_to_kg).toString();
          }
        }

        dates = output.dates;

        standard_dates = output.standard_dates;

        standard_centiles = output.standard_centiles;

        projected_dates = output.projected_dates;

        projected_weights = output.projected_weights;

        var full_grown_weight = projected_weights[projected_weights.length - 1];

        var full_grown_date = projected_dates[projected_dates.length - 1];
        
        console.log(full_grown_weight)
        if (!full_grown_weight){
          dashboard.innerHTML = output.html +'<p style="color:red;font-size:2em;">It appears that your dog is too young for us to predict its growth, <b>come back again next week</b> with the latest weight of your dog.</p>';
        }
        else{
          if (dog_weight_unit == 'lbs'){ //for projected weight
            full_grown_weight = full_grown_weight+ ' Pounds';
          }
          if (dog_weight_unit == 'kg'){ //for projected weight
            full_grown_weight = full_grown_weight*pounds_to_kg+ ' Kg';
          }
          dashboard.innerHTML = output.html +'<p style="color:green;font-size:2em;">Predicted Full Grown Weight is '+full_grown_weight+' </p>';
        }
        
        if (dog_weight_unit == 'lbs'){ //for projected weights
          //do nothing default is lbs
        }

        if (dog_weight_unit == 'kg'){  //for projected weights
          for (let i = 0; i < output.projected_weights.length; i++) {
            projected_weights[i] = (output.projected_weights[i]*pounds_to_kg).toString();
          }
        }


        //find if there are crossings
        var lower_bound_centiles = output.lower_bound_centiles;
        var min_centile = Math.max(...lower_bound_centiles);

        var growth_trend_html = '<p style="color:green;font-size:2em;">You Dog is Growing Normally. Keep up the Good Work!</p>'
        for (let i = 0; i < lower_bound_centiles.length; i++) { 
          if ((lower_bound_centiles[i] - min_centile) == 0){

            continue;
          }
          else{
            growth_trend_html = '<p style="color:red;font-size:2em;">You Dog May NOT be Growing Normally.</p>'
          }
        }

        growth_trend_html = ""; //override for now

        dashboard.innerHTML = dashboard.innerHTML + growth_trend_html;

        dashboard.innerHTML = dashboard.innerHTML + '<h3>Projected Weight and Dates</h3>';
        
        for (let i = 0; i < projected_weights.length; i++) { 
          dashboard.innerHTML = dashboard.innerHTML +'<p>'+projected_dates[i]+'     :      '+projected_weights[i]+' '+dog_weight_unit+'</p>';
        }
        

        
        if (dog_weight_unit == 'lbs'){ //for centiles
          //do nothing default is lbs
        }

        if (dog_weight_unit == 'kg'){  //for centiles 

          for (let i = 0; i < output.standard_centiles.length; i++) {
            for (let j = 0; j < output.standard_centiles[i].length; j++) {
              standard_centiles[i][j] = (output.standard_centiles[i][j]*pounds_to_kg).toString();
            }
          }
        }
        //console.log(weights)
        //console.log(dates)
        //console.log(output.standard_centiles);
        
        standard_centiles =standard_centiles[0].map((_, colIndex) => standard_centiles.map(row => row[colIndex])); //transpose to make each row a centile data
        console.log(output);

        
        var projected_trace = {
        x:projected_dates,
        y: projected_weights,
        type: 'scatter',
        name:'Your Dog`s Future Growth Pattern',
        line: {
              dash: 'dot',
              color: 'rgb(0, 0, 0)',
              width: 5
            }
        };


        var user_trace = {
        x:dates,
        y: weights,
        type: 'scatter',
        name: 'Your Dog`s Current Growth Pattern',
        line: {
              color: 'rgb(0, 0, 255)',
              width: 5,
            }
        };

        const standard_trace_centiles = [];
        for (let i = 0; i < 9; i++) {

            standard_trace_centiles[i] = {
            x:standard_dates,
            y: standard_centiles[i],
            type: 'scatter',
            name:'Standard Growth Curve '+(i+1)

          };
        }

        var layout = {showlegend: true,
          /*
          annotations: [
            {
              x: full_grown_date,
              y: full_grown_weight,
              showarrow: false,
              text: 'Full Grown',
            }]
            */
        };
        var result = {
            xref: 'paper',
            x: projected_dates[projected_dates.length - 1],
            y: projected_weights[projected_weights.length - 1],

            text: 'texttest',
            showarrow: true,

          };
        //layout.annotations.push(result);
        var data = [user_trace,projected_trace];

        Plotly.newPlot('graph_canvas', data.concat(standard_trace_centiles),layout);
          
        } //end of success function
      });  //end of ajax function    

}

function editClick(id){  //id is the button id that will be later used to identify the button
      edit_btn_clicked = document.getElementById('editbtn'.concat(id));
      weight_element_referenced = document.getElementById('weight'.concat(id));
      if (edit_btn_clicked.textContent == "Edit"){
        weight_element_referenced.removeAttribute('readonly');
        edit_btn_clicked.textContent='Done';
        return;
      }
      if (edit_btn_clicked.textContent == "Done"){
        new_weight = weight_element_referenced.value;

        weight_element_referenced.readOnly = true;
        edit_btn_clicked.textContent='Edit';

        if (dog_weight_unit == 'kg'){
          new_weight = new_weight * kg_to_pounds; // always save to db in unit of pounds
        }
        $.ajax({ url: url,
         data: {action: "update_dog_weight","id":id,"new_weight":new_weight},
         type: 'post',
         success: function(output,status,jqXHR) {
					console.log('dog weight updated');
          update_dashboard();
            
          }
        });

        return;
      }
    
    }
function deleteClick(id){
      $.ajax({ url: url,
         data: {action: "delete_dog_weight","id":id},
         type: 'post',
         success: function(output,status,jqXHR) {
          delete_btn_clicked = document.getElementById('deletebtn'.concat(id)).remove();
          edit_btn_referenced = document.getElementById('editbtn'.concat(id)).remove();
          weight_element_referenced = document.getElementById('weight'.concat(id)).remove();
          date_element_referenced = document.getElementById('date'.concat(id)).remove();
					console.log('dog weight updated');
            update_dashboard();
            
                  }
        });
        
        

    }
function addClick(dog_id_param){
  console.log("The current dog id parma: ".concat(dog_id_param));
  new_weight = document.getElementById('new_weight').value;
  new_date = document.getElementById('new_date').value;

  if (dog_weight_unit == 'kg'){
    new_weight = new_weight * kg_to_pounds; // prepping for next ajax call, we always add weight in lbs unit
  }
  $.ajax({ url: url,
         data: {action: "add_dog_weight","new_weight":new_weight,"new_date":new_date,"dog_id_param":dog_id_param},
         type: 'post',
         success: function(output,status,jqXHR) {

            update_dashboard();
            
                  }
        });  
}
function getadog(){
  var dog_ids = document.getElementById("dog_name_id");

  dog_id_param = dog_ids.value;
  update_dashboard();
       
}
function weightunitchange(){

  var weight_unit = document.getElementsByName('weight_unit_option');

  for (var i = 0, length = weight_unit.length; i < length; i++) {
    if (weight_unit[i].checked) {
      // do whatever you want with the checked radio
      dog_weight_unit = weight_unit[i].value;
      update_dashboard();

      // only one radio can be logically checked, don't check the rest
      break;
    }
  }




}
function updatedoggender(){

  var dog_ids = document.getElementById("dog_name_id");

  dog_id_param = dog_ids.value;

  var dog_gender_ids = document.getElementById("dog_gender_id");

  new_gender = dog_gender_ids.value;

  console.log("new_gender")
  console.log(new_gender)

  console.log("dog_id_param")
  console.log(dog_id_param)

  $.ajax({ url: url,
         data: {action: "update_dog_gender","new_gender":new_gender,"id":dog_id_param},
         type: 'post',
         success: function(output,status,jqXHR) {
            console.log(output);
            update_dashboard();
            
                  }
        });  
       
}

//
function updatedogbreedweightgroup(){

var dog_ids = document.getElementById("dog_name_id");

dog_id_param = dog_ids.value;

var dog_weight_groups = document.getElementById("dog_breed_weight_group");

new_weight_group = dog_weight_groups.value;

console.log("new_weight_group")
console.log(new_weight_group)

console.log("dog_id_param")
console.log(dog_id_param)

$.ajax({ url: url,
       data: {action: "update_dog_weight_group","new_weight_group":new_weight_group,"id":dog_id_param},
       type: 'post',
       success: function(output,status,jqXHR) {
          console.log(output);
          update_dashboard();
          
                }
      });  
     
}

function updatedogbreed(){

var dog_ids = document.getElementById("dog_name_id");

dog_id_param = dog_ids.value;

var dog_breed_ids = document.getElementById("dog_breed_id");

new_breed = dog_breed_ids.value;

console.log("new_breed")
console.log(new_breed)

console.log("dog_id_param")
console.log(dog_id_param)

$.ajax({ url: url,
       data: {action: "update_dog_breed","new_breed":new_breed,"id":dog_id_param},
       type: 'post',
       success: function(output,status,jqXHR) {
          console.log(output);
          update_dashboard();
          
                }
      });  
     
}
</script>

<?php

//create short variable names
if (!isset($_POST['email']))  {
  //if not isset -> set with dummy value 
  $_POST['email'] = " "; 
}
$email = $_POST['email'];
if (!isset($_POST['passwd']))  {
  //if not isset -> set with dummy value 
  $_POST['passwd'] = " "; 
}
$passwd = $_POST['passwd'];

if ($email && $passwd && !isset($_SESSION['valid_user'])) {
// they have just tried logging in
  try  {
    $user_priv = login($email, $passwd);
    // if they are in the database register the user id
    $_SESSION['valid_user'] = $email;
    $_SESSION['user_priv'] = $user_priv;

  }
  catch(Exception $e)  {
    // unsuccessful login
    do_html_header('Problem:');
    echo 'You could not be logged in.<br>
          You must be logged in to view this page.';
    do_html_url('login.php', 'Login');
    do_html_footer();
    exit;
  }
}

do_html_header('Home');
check_valid_user();
// get the bookmarks this user has saved
if ($url_array = get_user_urls($_SESSION['valid_user'])) {
  display_user_urls($url_array);
}

function display_dog_weights(){
  if (isset($_SESSION['valid_user']))  {

    $valid_user = $_SESSION['valid_user'];
    
    $conn = db_connect();
    echo '<div id="dashboard-div"><p>Loading...Please Wait</p></div>';

    ?>
    <script>
    var valid_user = <?php echo json_encode($valid_user); ?>;
    
    //dog_id_param = get_default_dog_id();

    update_dashboard();
    
    

    </script>
    
    <?php


  }
}


display_dog_weights();
// give menu of options
display_user_menu();

do_html_footer();
?>



    
    


