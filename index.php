<html>
     <head>
          <meta charset="utf8">
          <link href="ui/jquery-ui.css" rel="stylesheet">
          <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,400,300,600' rel='stylesheet' type='text/css'>
          <script src="ui/external/jquery/jquery.js"></script>
          <script src="ui/jquery-ui.js"></script> <!-- Utilizamos el framework jQuery UI -->

          <style>
               *{
               font-family: "Open Sans";    
               }
               
               #accordion, .abierto{
                   height: auto !important;
                   padding:0 40px !important;
               }
               h3{
                   color:white !important;
               }
               body{
                   background:darkgrey;
               }
               .autores{
                   margin: 50px auto;
                   width: 230px;
               }
               .autores img{
                   border-radius:50px;
                   margin:0 5px;
               }
          </style>
     </head>

     <?php

     function get_string_between($string, $start, $end) { //FUNCIÓN PARA COGER UN STRING DENTRO DE UN STRING
         $string = " " . $string;
         $ini = strpos($string, $start);
         if ($ini == 0)
             return "";
         $ini += strlen($start);
         $len = strpos($string, $end, $ini) - $ini;
         return substr($string, $ini, $len);
     }

     if (isset($_POST['username']) && isset($_POST['password'])) { //Si ha hecho Login
         $servidor_LDAP = "ldap://52.24.210.244:389";
         $servidor_dominio = "toca.cat";
         $ldap_dn = "ou=ibadia,dc=toca,dc=cat";
         $usuario_LDAP = $_POST['username'];
         $contrasena_LDAP = $_POST['password'];
         
         //CONECTAMOS A LDAP
         
         $ldap = ldap_connect($servidor_LDAP);
         ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
         ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
         
         //Si la conexión es correcta
         if ($ldap) {
             $ldap_done = ldap_bind($ldap, $usuario_LDAP . "@" . $servidor_dominio, $contrasena_LDAP);
             if ($ldap_done) {
                 
                 //Hacemos una busqueda de todos los grupos
                 $results2 = ldap_search($ldap, $ldap_dn, "(objectcategory=group)", array("distinguishedname", "name"));
                 $entries2 = ldap_get_entries($ldap, $results2);
                 
                 $resultuser = ldap_search($ldap, $ldap_dn, "(name=$usuario_LDAP)", array("memberof"));
                 $entriesuser = ldap_get_entries($ldap, $resultuser);
                 
                 //Hacemos una busqueda del grupo del usuario
                 
                 $grupo = $entriesuser[0]["memberof"][0];
                 $grupo = get_string_between($grupo, "CN=", ",");
                 
                 //$grupo es el Grupo del current user
                 
                 $countgroups = array_shift($entries2);
                 
                 //Creamos un accordion de todos los grupos
                 echo "<div class='accordion'>";
                 foreach ($entries2 as $item) {
                     $namegroup = $item["name"][0];
                     echo "<h3 class='grupo'>" . $namegroup . "</h3>";
                     echo"<div></div>";
                 }
                 echo "</div>";
             } else {
                 echo "<br><br>No se ha podido autenticar con el servidor LDAP: "; //Mensaje de Error
             }
         } else {
             echo "<br><br>No se ha podido realizar la conexión con el servidor LDAP: " .
             $servidor_LDAP;
         }
     } else {
         ?>
         <form action="" method="POST">
              <label for="username">Usuario: </label><input id="username" type="text" name="username" /> 
              <label for="password">Password: </label><input id="password" type="password" name="password" />
              <input type="submit" name="submit" value="Login" />
         </form>
         <?php
     }
     ?>
     <div class="autores"> <!-- Autores-->
          <img src='http://agora.xtec.cat/iesbadia/moodle/pluginfile.php/70081/user/icon/xtec2/f1?rev=37432' alt="Joshua" />
          <img src='http://agora.xtec.cat/iesbadia/moodle/pluginfile.php/70261/user/icon/xtec2/f1?rev=37444' alt="Polo" />
     </div>
     <script>
         var grupo = "<?= $grupo ?>";

         $(document).ready(function () {

             $(".accordion").accordion({
                 active: false,
                 collapsible: true
             });
             
             $(".accordion h3").each(function(){
                 var currentgroup = window.grupo;
                 var title= $(this).text();
                 if(title==currentgroup) $(this).css("background","cadetblue");
                 else if (currentgroup=="sysops") $(this).css("background","cadetblue");
                 else $(this).css("background","crimson");
                 
             });

             $(".grupo").click(function () {
                 var titulo = $(this).text();
                 var currentgroup = window.grupo;
                 if (titulo == currentgroup || currentgroup == "sysops"){
                     var grupo = $(this);
                     $.ajax({
                         method: "POST",
                         url: "ajax.php",
                         dataType: "JSON",
                         data: {group: titulo}
                     })
                             .done(function (data) {
                                 var div = grupo.next();
                                 div.addClass("abierto");
                                 div.html("<div></div>");
                                 var hijo = div.find("div");
                                 for (var i = 0; i < data.length; i++) {
                                     hijo.append("<p>" + data[i] + "</p>");
                                 }
                             });
                 }
                 else{
                 alert('No tienes permisos para ver este grupo');
                 }


             });


         });

     </script>
</html>



