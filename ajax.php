<?php

if (isset($_POST['group'])) { //SI EXISTE POST GROUP
    $namegroup = $_POST['group']; //$namegroup coge el valor de POST GROUP
    $servidor_LDAP = "ldap://52.24.210.244:389";
    $servidor_dominio = "toca.cat";
    $ldap_dn = "ou=ibadia,dc=toca,dc=cat";
    $usuario_LDAP = "user1";
    $contrasena_LDAP = "Platano123$"; /*  NOS LOGAMOS COMO USER1*/
    $usuarios = [];
    $ldap = ldap_connect($servidor_LDAP);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0); 
    if ($ldap) {//NOS CONECTAMOS A LDAP 
        $ldap_done = ldap_bind($ldap, $usuario_LDAP . "@" . $servidor_dominio, $contrasena_LDAP);
        if ($ldap_done) {
            $results3 = ldap_search($ldap, $ldap_dn, "(&(objectClass=user)(memberOf=CN=$namegroup,$ldap_dn))", array("name"));
            $entries3 = ldap_get_entries($ldap, $results3); //HACEMOS UN SEARCH DE LOS USUARIOS DEL GRUPO $namegroup
            $countusers = array_shift($entries3);
            foreach ($entries3 as $user) {
                array_push($usuarios, $user["name"][0]); //CADA PROPIEDAD NAME DE CADA OBJETO USUARIO SE INTRODUCE EN EL ARRAY $usuarios
            }
            $usuarios = array_slice($usuarios, 0,10); //ACORTAMOS ESTE ARRAY A 10
            echo json_encode($usuarios); //PRINTAMOS EL RESULTADO EN JSON
        }
    }
}
?>