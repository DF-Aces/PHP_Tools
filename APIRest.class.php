<?php
 

class ApiRest extends ConnexionBDD
{


    /* " -------------------------------------AIDE --------------------------------------------<br/>
                            -Cette class herite de la classe ConnexionBDD et est incompléte elle sera ameliorer ainsi que sa classe mere dans le futur
                            -Si l'objet ne prend pas de paramètres il appelle l'aide ( équivaut à un NOMDELACLASSE::HELP)<br/>
                            -La methode getAllData prend comme paramétre:
                                *La table dans laquelle on veut recuperer les données
                            -La méthode insertData prend comme paramétre:<br/>
                                *La table dans laquelle on souhaite inserer les données<br/>
                                *Un tableau de valeur  qui contient les valeurs à ajouter ( pour les auto_increment, la valeur a rentrer sera '')<br/>
                            -La methode deleteData est similaire a getAllData()
                            -La methode verification() prend comme parametre:
                                *La table
                                *Un tableau representant le nom des colonnes de la table dans lesquels ce situent le mot de passe et l'utilisateur
                                *Le mot de passe et l'utilisateur

                            ";*/

    
    function __construct($name_of_db=null,$host_url=self::LOCALHOST,$login=null,$password=null)
    {
        parent::__construct($name_of_db,$host_url,$login,$password);
    }


    public function getAllData($table=null)
    {
        if(!empty($table))
        {
            $answer = parent::selectAll($table);
            $this->response($answer);
        }else
        {
            $this->response([["Response"=>"500",
                "Success"=>false]]);
        }
    }

    public function deleteData($tb=null, $column=null,$id)
    {
        if(!empty($tb))
        {
            parent::createBDD()->query("DELETE FROM ".$tb." WHERE ".$column."='".$id."'");

            $this->response([["Response"=>200]]);
        }
    }

    //Condition prend forcement et strictement deux parametre la colonne de table sql et le parametre de la condition (ex [id,2])
    public function updateData($t=null, $col=null,$value,$condition=[])
    {
        $sql="";
        if(!empty($t))
        {
            //Si aucune condition n'est donnée
            if(!empty($condition))
            {
                if(count($condition)==2)
                {
                    $sql="UPDATE ".$t." SET ".$col." = '".$value."' WHERE ".$condition[0]." = '".$condition[1]."'";
                    $this->response([
                        "Success"=>200,
                    ]);
                }else{
                    $this->response([
                        "Success"=>503,
                        "Details"=>"Argument manquant"
                    ]);
                }
            }
            parent::createBDD()->query($sql);
        }
    }

    public function insertData($table=null, $column=null)
    {
        if(!empty($table) && !empty($column))
        {
            $re = parent::insertRow($table,$column);
            if($re != 2 || $re !=false)
            {
                $this->response([["Response"=>200,
                "Success"=>$re]]);
            }else{
                $this->response([["Response"=>"500",
                "Details"=>"Creation impossible",
                "Erreur"=>$re,
                "Success"=>false]]);
            }
        }
    }


    public function verification($table=null,$r=["user"=>"","password"=>""], $user,$password)
    {
        if(!empty($table) && !empty($r))
        {
        $rows["user"] = $r[0];
        $rows["password"] = $r[1];
         $req =parent::createBDD()->prepare("SELECT * FROM ".$table." WHERE ".$rows["user"]."= ? AND ".$rows["password"]."= ?");
         $req->execute(array(
             $user,
             $password
         ));
         $rep = $req->fetchAll();
         $req->closeCursor();

         if($rep)
         {
             $rep["Response"]=200;
             $rep["User"]= true;
             return $rep;
         }else{
            $rep["Response"]=503;
            $rep["User"]= false;
             return $rep;
         }
        }
    }

    public function response($tab)
    {
        header('Content-Type: application/json');
        echo $this->jsonParser($tab);
    }

    //Creer un format Json lisible a partir du tableau donné
    private function jsonParser($tabs)
    {
        foreach($tabs as &$tab)
        {
            if(is_array($tab))
            {
                foreach($tab as $key => $element)
                {
                    if(is_numeric($key))
                    {
                        unset($tab[$key]);
                    }
                }
            }
            unset($tab);
        }

        $json = json_encode($tabs,JSON_FORCE_OBJECT);

        return $json;
    }

}