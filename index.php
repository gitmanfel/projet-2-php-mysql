<?php

/*Sanitisation*/
$options = array(
  'tache'         => FILTER_SANITIZE_STRING,
  'tache_ligne'   => FILTER_SANITIZE_STRING
);
$result = filter_input_array(INPUT_POST, $options);
$_POST["tache"] = filter_var($_POST["tache"], FILTER_SANITIZE_STRING);
$_POST["tache_ligne"] = filter_var($_POST["tache_ligne"], FILTER_SANITIZE_STRING);
$_POST["ajouter"] = filter_var($_POST["ajouter"], FILTER_SANITIZE_STRING);
$_POST["submit"] = filter_var($_POST["submit"], FILTER_SANITIZE_STRING);



try
{
  // On se connecte à MySQL
    $bdd=new PDO('mysql:host=localhost;dbname=todolist;charset=utf8','root','root');
}
catch(Exception $e)
{
  echo "TEST";
	// En cas d'erreur, on affiche un message et on arrête tout
        die($e->getMessage());
}

/*fin Sanitisation*/
//Requête POST:
//vérification des valeurs après la Sanitisation
if($result != null && $result != FALSE && $_SERVER['REQUEST_METHOD']=='POST')
{
  if(isset($_POST['ajouter'])&& $_POST['ajouter']=="Ajouter")
  {
    $tache = filter_var($_POST["tache"], FILTER_SANITIZE_STRING);

    $req = $bdd->prepare('INSERT INTO afaire(afaire,terminer,echeance,heure) VALUES(:afaire2,:terminer,:echeance,:heure)');
     $req->execute(array(
         'afaire2'       => $tache,
         'terminer'      => 0,
         'echeance'      =>$_POST['date'],
         'heure'         =>$_POST['time']
         ));

  }
  if(isset($_POST['enregistrer'])&& $_POST['enregistrer']=="Enregistrer")
  {

      foreach ($_POST["check"] as $key => $value) {
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        $req = $bdd->prepare('UPDATE afaire SET terminer= 1 WHERE afaire = :value');
         $req->execute(array(
             'value'       => $value
             ));
      }

  }

}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link href="style.css" rel="stylesheet">
  <title>My To-do List with Mysql</title>
</head>

<body>
  <div class="contenu">
    <header>
      <img class="displayed" src="logo2.png" alt="My To Do List">
    </header>

    <section>
      <fieldset class="afaire">
        <legend>
          <h2>A faire</h2></legend>
          <form action="index.php" method="POST">

            <div class="dropper"


                <?php
                // $reponse = $bdd->query('SELECT * FROM taches ORDER BY Sort ASC');
                    $resultat = $bdd->query('select * from afaire');
                    while ($donnees = $resultat->fetch())
                    {
                      // $echeance = $donnees["echeance"];
                      // $date = strtotime($echeance);
                      if($donnees['terminer']==0)
                      {
                        echo "<label for='choix'>";
                        echo "<input type='checkbox' name='check[]' value='".($donnees['afaire'])."'/>";
                        echo ($donnees['afaire']);
                        echo "</label><br />";
                      }
                    }

                      $resultat->closeCursor();
                ?>


              <input class="bouton" type="submit" name="enregistrer" value="Enregistrer">
            </div>
            </form>
          </fieldset>
          <fieldset class="archives">
            <legend>
              <h2>Archives</h2></legend>

              <div class="dropper">

                   <?php
                  $resultat = $bdd->query('select * from afaire');
                  while ($donnees = $resultat->fetch())
                  {
                    if($donnees['terminer']==1)
                    {
                      echo "<label class='checked' for='choix'>";
                      echo "<input type='checkbox' name='check[]' value='".($donnees['afaire'])."' checked disabled/>";
                      echo ($donnees['afaire']);
                      echo "</label><br />";
                    }
                  }

                    $resultat->closeCursor();
                    ?>


            </div>
          </fieldset>
        </section>
        <section>
          <fieldset class="tache">
            <legend>
              <h2>Nouvelle tâche : </h2></legend>
              <form action="index.php" method="POST">
                <textarea name="tache" value="" placeholder="Écrire ici la nouvelle tâche"></textarea></br>
                <input type="date" name="date" value="">
                <input type="time" name="time" value="">
                <div class="erreur">
                  <?php echo $errors; ?>
                </div>
                <br>
                <input class="bouton" type="submit" name="ajouter" value="Ajouter">
            </form>
              </fieldset>


          </section>
        </div>
      </body>
      <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>
      <script>

      (function(){
        var dndHandler = {
          draggedElement: null,
          applyDragEvents: function(element) {
            element.draggable = true;
            var dndHandler = this;
            element.addEventListener('dragstart', function(e) {
              dndHandler.draggedElement = e.target;
              e.dataTransfer.setData('text/plain', '');
            }, false);
          },
          applyDropEvents: function(dropper){
            dropper.addEventListener('dragover', function(e){
              e.preventDefault();
              this.className = 'dropper drop_hover';
            }, false);
            dropper.addEventListener('dragleave', function(){
              this.className = 'dropper';
            });
            var dndHandler = this;
            dropper.addEventListener('drop', function(e){
              var target = e.target,
              draggedElement = dndHandler.draggedElement,
              clonedElement = draggedElement.cloneNode(true);
              while(target.className.indexOf('dropper') == -1){
                target = target.parentNode;
              }
              target.className = 'dropper';
              clonedElement = target.appendChild(clonedElement);
              dndHandler.applyDragEvents(clonedElement);
              draggedElement.parentNode.removeChild(draggedElement);
            });
          }
        };
        var elements = document.querySelectorAll('.draggable'),
        elementsLen = elements.length;
        for(var i = 0 ; i < elementsLen ; i++){
          dndHandler.applyDragEvents(elements[i]);
        }
        var droppers = document.querySelectorAll('.dropper'),
        droppersLen = droppers.length;
        for(var i = 0 ; i < droppersLen ; i++) {
          dndHandler.applyDropEvents(droppers[i]);
        }
      })();
      function focuscheck(index)
      {
        $.post(
          'index.php',
          {
            "tache_ligne":index,
            "submit":"Enregistrer"
          },
          function(data)
          {
            document.location.href="index.php";
          },
          'text'
        );
      };
      </script>


      </html>
