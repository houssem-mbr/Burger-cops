<?php

    require 'connexion.php';

    if(!empty($_GET['id'])) 
    {
        $id = checkInput($_GET['id']);
    }

    $nameError = $descriptionError = $priceError = $categoryError = $imageError = $name = $description = $price = $category = $image = "";

    if(!empty($_POST)) 
    {
        $name               = checkInput($_POST['name']);
        $description        = checkInput($_POST['description']);
        $price              = checkInput($_POST['price']);
        $category           = checkInput($_POST['category']); 
        $image              = checkInput($_FILES["image"]["name"]);
        $imagePath          = '../images/'. basename($image);
        $imageExtension     = pathinfo($imagePath,PATHINFO_EXTENSION);
        $isSuccess          = true;
       
        if(empty($name)) 
        {
            $nameError = 'Ce champ ne peut pas être vide';
            $isSuccess = false;
        }
        if(empty($description)) 
        {
            $descriptionError = 'Ce champ ne peut pas être vide';
            $isSuccess = false;
        } 
        if(empty($price)) 
        {
            $priceError = 'Ce champ ne peut pas être vide';
            $isSuccess = false;
        } 
         
        if(empty($category)) 
        {
            $categoryError = 'Ce champ ne peut pas être vide';
            $isSuccess = false;
        }
        if(empty($image)) // le input file est vide, ce qui signifie que l'image n'a pas ete update
        {
            $isImageUpdated = false;
        }
        else
        {
            $isImageUpdated = true;
            $isUploadSuccess =true;
            if($imageExtension != "jpg" && $imageExtension != "png" && $imageExtension != "jpeg" && $imageExtension != "gif" ) 
            {
                $imageError = "Les fichiers autorises sont: .jpg, .jpeg, .png, .gif";
                $isUploadSuccess = false;
            }
            if(file_exists($imagePath)) 
            {
                $imageError = "Le fichier existe deja";
                $isUploadSuccess = false;
            }
            if($_FILES["image"]["size"] > 500000) 
            {
                $imageError = "Le fichier ne doit pas depasser les 500KB";
                $isUploadSuccess = false;
            }
            if($isUploadSuccess) 
            {
                if(!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) 
                {
                    $imageError = "Il y a eu une erreur lors de l'upload";
                    $isUploadSuccess = false;
                } 
            } 
        }
         
        if (($isSuccess && $isImageUpdated && $isUploadSuccess) || ($isSuccess && !$isImageUpdated)) 
        { 
            $db = Database::connect();
            if($isImageUpdated)
            {
                $statement = $db->prepare("UPDATE items  set name = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ?");
                $statement->execute(array($name,$description,$price,$category,$image,$id));
            }
            else
            {
                $statement = $db->prepare("UPDATE items  set name = ?, description = ?, price = ?, category = ? WHERE id = ?");
                $statement->execute(array($name,$description,$price,$category,$id));
            }
            Database::disconnect();
            header("Location: index.php");
        }
        else if($isImageUpdated && !$isUploadSuccess)
        {
            $db = Database::connect();
            $statement = $db->prepare("SELECT * FROM items where id = ?");
            $statement->execute(array($id));
            $item = $statement->fetch();
            $image          = $item['image'];
            Database::disconnect();
           
        }
    }
    else 
    {
        $db = Database::connect();
        $statement = $db->prepare("SELECT * FROM items where id = ?");
        $statement->execute(array($id));
        $item = $statement->fetch();
        $name           = $item['name'];
        $description    = $item['description'];
        $price          = $item['price'];
        $category       = $item['category'];
        $image          = $item['image'];
        Database::disconnect();
    }

    function checkInput($data) 
    {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }
    include('update.phtml');
?>



