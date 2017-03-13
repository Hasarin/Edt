<!DOCTYPE html> 
<html>
<head>
<meta charset='utf-8' />
<link href='../css/fullcalendar.min.css' rel='stylesheet' />
<link href='../css/fullcalendar.print.css' rel='stylesheet' media='print' />
<link href='../css/jquery.qtip.min.css' rel='stylesheet' />
<script src='../js/lib/jquery.min.js'></script>
<script src='../js/lib/moment.min.js'></script>
<script src='../js/fullcalendar.min.js'></script>
    <script src='../js/lib/jquery.qtip.min.js'></script>
    <script src='../js/lang/fr.js'></script>
    <script src='../js/locale-all.js'></script>
	<title></title>
</head>
<body>

	test
   
<script type="text/javascript">
$(document).ready(function() {
    charger_liste_modules() ;
    charger_liste_groupes() ;
    charger_liste_profs() ;
    charger_liste_salle();

}) ;



function charger_liste_cours(module,check) {
    console.log(module);

    var req = new XMLHttpRequest();
    req.open('GET', 'http://localhost/Edt/rest/index.php/cours?module='+module , true);
    req.onreadystatechange = function() {
        if (req.readyState == 4 && (req.status == 200 || req.status == 0)) {
            var fragment = document.createDocumentFragment();
            liste_modules = JSON.parse(req.response);
            $.each(liste_modules, function() {
                console.log(this.codeEnseignement);

                fragment.appendChild($("<option />").val(this.codeEnseignement).text('('+this.nom+') '+this.alias)[0]);
            });
            if(check=="lb_cours"){
                document.getElementById("lb_cours").innerHTML = "";
                $("#lb_cours").append(fragment); 
                } 
            else{
                document.getElementById("cours1").innerHTML = "";
                $("#cours1").append(fragment);  
            }

             }
    } ;
    req.send(null);
    //btn = $('#ihm_Charger').css('display','block');
}


function charger_liste_modules() {

    var req = new XMLHttpRequest();
    req.open('GET', 'http://localhost/Edt/rest/index.php/modules' , true);
    req.onreadystatechange = function() {
        if (req.readyState == 4 && (req.status == 200 || req.status == 0)) {
            var fragment = document.createDocumentFragment();
            liste_modules = JSON.parse(req.response);


            $.each(liste_modules, function() {
                fragment.appendChild($("<option />").val(this.nom).text('('+this.nom+') '+this.alias)[0]);
            });
            $("#lb_module").append(fragment);   
             $.each(liste_modules, function() {
                fragment.appendChild($("<option />").val(this.nom).text('('+this.nom+') '+this.alias)[0]);
            });
            $("#module1").append(fragment);       }
    } ;
    req.send(null);

    btn = $('#ihm_Charger').css('display','block');
}

function charger_liste_groupes() {
    var req = new XMLHttpRequest();
    req.open('GET', 'http://localhost/Edt/rest/index.php/groupes' , true);
    req.onreadystatechange = function() {
        if (req.readyState == 4 && (req.status == 200 || req.status == 0)) {
            var fragment = document.createDocumentFragment();

            liste_groupes = JSON.parse(req.response);
            $.each(liste_groupes, function() {
                fragment.appendChild($("<option />").val(this.nom).text('('+this.nom+') '+this.alias)[0]);
            });
            $("#lb_groupe").append(fragment);  
             $.each(liste_groupes, function() {
                fragment.appendChild($("<option />").val(this.nom).text('('+this.nom+') '+this.alias)[0]);
            }); 
            $("#groupe1").append(fragment);    }
    } ;
    req.send(null);
}

function charger_liste_salle() {
    var req = new XMLHttpRequest();
    req.open('GET', 'http://localhost/Edt/rest/index.php/salle' , true);
    req.onreadystatechange = function() {
        if (req.readyState == 4 && (req.status == 200 || req.status == 0)) {
            var fragment = document.createDocumentFragment();
            liste_salle = JSON.parse(req.response);

            $.each(liste_salle, function() {
                fragment.appendChild($("<option />").val(this.codeSalle).text(this.nom)[0]);
            });
            $("#lb_salle").append(fragment);  
             $.each(liste_salle, function() {
                fragment.appendChild($("<option />").val(this.codeSalle).text(this.nom)[0]);
            });
            $("#salle1").append(fragment);         }
    } ;
    req.send(null);
}

function charger_liste_profs() {
    var req = new XMLHttpRequest();
    req.open('GET', 'http://localhost/Edt/rest/index.php/profs' , true);
    req.onreadystatechange = function() {
        if (req.readyState == 4 && (req.status == 200 || req.status == 0)) {
            var fragment = document.createDocumentFragment();
            liste_profs = JSON.parse(req.response);

            $.each(liste_profs, function() {
                fragment.appendChild($("<option />").val(this.codeProf).text(this.alias)[0]);
            });

            $("#lb_prof").append(fragment); $.each(liste_profs, function() {
                fragment.appendChild($("<option />").val(this.codeProf).text(this.alias)[0]);
            });    
            $("#prof1").append(fragment);     
}
    } ;
    req.send(null);
}

function supprimer(){
    var req = new XMLHttpRequest();
    var parm = "";
    var prefix = "?" ;
    var requestString ;
     var module = document.getElementById("lb_module").value;
        parm = parm + prefix + "module=" + module ;
        prefix = "&" ;
    
        var groupe = document.getElementById("lb_groupe").value;
        parm = parm + prefix + "groupe=" + groupe ;
        prefix = "&" ;
    
        var prof = document.getElementById("lb_prof").value;
        parm = parm + prefix + "prof=" + prof ;
        prefix = "&" ;
    
        var salle = document.getElementById("lb_salle").value;
        parm = parm + prefix + "salle=" + salle ;
        prefix = "&" ;
    
        var heure = document.getElementById("heure").value;
        parm = parm + prefix + "heure=" + heure ;
        prefix = "&" ;
    
        var cours = document.getElementById("lb_cours").value;
        parm = parm + prefix + "cours=" + cours ;
        prefix = "&" ;
   
        var date = document.getElementById("date").value ;
        parm = parm + prefix + "date=" + date ;
        prefix = "&" ;

        var date1 = document.getElementById("date1").value ;
        parm = parm + prefix + "date1=" + date1 ;
        prefix = "&" ;

        var heure1 = document.getElementById("heure1").value ;
        parm = parm + prefix + "heure1=" + heure1 ;
        prefix = "&" ;

        var cours1 = document.getElementById("cours1").value ;
        parm = parm + prefix + "cours1=" + cours1 ;
        prefix = "&" ;

        var prof1 = document.getElementById("prof1").value ;
        parm = parm + prefix + "prof1=" + prof1 ;
        prefix = "&" ;

        var module1 = document.getElementById("module1").value ;
        parm = parm + prefix + "module1=" + module1 ;
        prefix = "&" ;
       
        var groupe1 = document.getElementById("groupe1").value ;
        parm = parm + prefix + "groupe1=" + groupe1 ;
        prefix = "&" ;
        
        var salle1 = document.getElementById("salle1").value ;
        parm = parm + prefix + "salle1=" + salle1 ;
        prefix = "&" ;
    
     requestString = "http://localhost/Edt/rest/index.php/modif" ;
    if (prefix == "&") {
        requestString = requestString + parm ;
    }
   
        req.open('GET', requestString, true);
        req.send(null);

        rep = JSON.parse(req.response);
        $.each(liste_salle, function() {
        console.log(this.cle1);
        });

        document.getElementById('affichage').innerHTML = req.cle1+"!!!";
            

        
}

function activate(check) {
    if(check=="lb_cours"){
                var module = $("#lb_module option:selected")[0].value ;
                charger_liste_cours(module,check) ;
            }
            else{
                 var module = $("#module1 option:selected")[0].value ;
                charger_liste_cours(module,check) ;
            }

}

</script>
	

	 <fieldset>
       <legend>le cours à modifier</legend> <!-- Titre du fieldset --> 

       
      
     MODULES  
            <select id='lb_module' onchange='activate("lb_cours")'></select>
       
      GROUPES  
            <select id='lb_groupe' ></select>
       
     PROFS   
            <select id='lb_prof' ></select>
       
      SALLES  
            <select id='lb_salle' ></select>
        
        <?php echo "HEURE :<input type='number' name='heure' id='heure' /> </br> "?>
        <?php echo "DATE :<input type='date' name='date' id='date' />  "?>
          
            COURS <select id='lb_cours' ></select>
        <input type='button' value='modif' id='btnCharger' onclick='supprimer()'/>

       

   </fieldset>

    <fieldset>
       <legend>Modification</legend> <!-- Titre du fieldset --> 

       
      
     MODULES  
            <select id='module1' onchange='activate("cours1")'></select>
       
      GROUPES  
            <select id='groupe1' ></select>
       
     PROFS   
            <select id='prof1' ></select>
       
      SALLES  
            <select id='salle1' ></select>
        
        <?php echo "HEURE :<input type='number' name='heure1' id='heure1' /> </br> "?>
        <?php echo "DATE :<input type='date' name='date1' id='date1' />  "?>
          
            COURS <select id='cours1' ></select>
        <input type='button' value='modif' id='btnCharger' onclick='supprimer()'/>

      

   </fieldset>

   <div id="affichage"></div> 

</body>
</html>