<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <title>Blog</title>
</head>
<body>
<style>
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 4px solid #fefefe;
        width: 80%; /* Could be more or less, depending on screen size */
    }

    #delete_modal{
        display: none;
    }

    .table_row:hover {
        background: #E3E3E3 !important;
    }
    .table_row{
        background: none !important;
    }

</style>


<?php
//__ Let's go !!
$pdo = dbConnection();

//$article=[];
//
//if(!$article == 'undefined')

//try {
//    // sql to create table
//    $table = "CREATE TABLE IF NOT EXISTS article (
//      id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//      title VARCHAR(255) NOT NULL,
//      subtitle VARCHAR(255) NOT NULL,
//      content LONGTEXT,
//      published_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//      updated_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
//      )";

    // use exec() because no results are returned
//    if(!$sql == 'undefined')
//    {
//        echo 'Table already exists';
//    }
//    $conn->exec($table);
//    echo "Table Article created successfully";
//
//} catch(PDOException $e) {
//    echo "Fission Mailed" . "<br>" . $e->getMessage();
//}
function dbConnection()
{
    if(isset($_SESSION['db'])){
        return $_SESSION['db'];
    }
    $servername = "db";
    $username = "root";
    $password = "Arpa3DataBase";
    $dbname = "jules";
    $port = 3306;

    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
// set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $_SESSION['db'] = $conn;
}

$conn = dbConnection();


function getArticleById($id)
{
    $pdo = dbConnection();
    $article = $pdo->query("SELECT * FROM Article WHERE id=".$id);

    return $article->fetch();
}
//Vérifier le format de l'email :
//$format = "SELECT * FROM users WHERE email NOT LIKE '%_@__%.__%'"
//if($format){
//
//}

function emptyInputSignup($name, $username, $email, $password, $passwordConfirm)
{
    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($passwordConfirm)) {
        echo "You must fill all the fields!";
    }
}

function invalidEmail($email)
{
    $result;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result = false;
    } else {
        $result = true;
    }
    return $result;
}

function invalidPassword($password, $passwordConfirm)
{
    $result;
    if(strlen($password) <= 8 && $password !== $passwordConfirm){
        $result = true;
    } else {
        $result = false;
    }
}

function createUser($pdo, $name, $username, $email, $password)
{
    $pdo = dbConnection();

    if(!empty($_POST["action"])) {

        $action = $_POST["action"];

        if ($action == "create_acc") {
            $sql = "INSERT INTO users(name, email, username, password) VALUES (:name, :email, :username, :password)";
            $sth = $pdo->prepare($sql);
            $sth->execute();

            if (!$pdo->prepare($sql)) {
                header("location: ../acc_create?error=stmtfailed");
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sth->bindParam(":name", $name, PDO::PARAM_STR, 64);
            $sth->bindParam(":email", $email, PDO::PARAM_STR, 64);
            $sth->bindParam(":username", $username, PDO::PARAM_STR, 64);
            $sth->bindParam(":password", $hashedPassword, PDO::PARAM_STR, 256);

            if (password_verify($password, $hashedPassword)){
                echo 'Valid password';
            } else{
                echo 'Invalid password';
            }

            $publisher_id = $pdo->lastInsertId();
            echo 'The user id ' . $publisher_id . ' has been signed up';

            header("location: ../?page=acc_create?error=none");
            exit();
        }
    }
}

function emailExists($conn, $email, $username)
{
    $sql = "SELECT * FROM users WHERE email= :email OR username= :username";
    $pdo = dbConnection();
    $sth = $pdo->prepare($sql);
    if (!$pdo->prepare($sql))
    {
        header("location: ../?page=acc_create?error=stmtfailed");
        exit();
    }

    $pdo->bindParam("email", $email, PDO::PARAM_STR);
    $pdo->bindParam("username", $username, PDO::PARAM_STR);
    $sth->execute();

    $resultData = $sth->fetch();

    if ($resultData->fetch(PDO::FETCH_ASSOC)){
        $result = true;
    } else{
        $result = false;
        return $result;
    }

    $pdo = null;
    $sth = null;
}

function signUp(){
    $pdo = dbConnection();

    if(!isset($_POST["submit"])) {

        $name = $_POST["name"];
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $passwordConfirm = $_POST["passwordRepeat"];


        if (emptyInputSignUp($name, $username, $email, $password, $passwordConfirm) !== false) {
            header("location: ../?page=acc_create?error=emptyinput");
            exit();
        }

        if (invalidEmail($email)) {
            header("location: ../?page=acc_create?error=invalidemail");
            exit();
        }

        if (emailExists($pdo, $email, $username) !== false) {
            header("location: ../?page=acc_create?error=usernametaken");
            exit();
        }

        if (invalidPassword($password, $passwordConfirm)) {
            header("location: ../?page=acc_create?error=invalidpassword");
            exit();
        }

        createUser($pdo, $name, $username, $email, $password);

    } else {
        header("location: ../?page=acc_create");
        exit();
    }
}

/*$function signIn(){
    //login
        if (isset($_POST['uemail']) && isset($_POST['password'])) {

            function validate($data)
            {

                $data = trim($data);

                $data = stripslashes($data);

                $data = htmlspecialchars($data);

                return $data;

            }

            $uemail = validate($_POST['uemail']);

            $pass = validate($_POST['password']);

            if (empty($uemail)) {

                header("Location: user_auth?error=User Email is required");

                exit();

            } else if (empty($pass)) {

                header("Location: user_auth?error=Password is required");

                exit();

            } else {

                $sql = "SELECT * FROM users WHERE email='$uemail' AND password='$pass'";

                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) === 1) {

                    $row = mysqli_fetch_assoc($result);

                    if ($row['user_email'] === $uemail && $row['password'] === $pass) {

                        echo "Logged in!";

                        $_SESSION['user_email'] = $row['user_email'];

                        $_SESSION['password'] = $row['password'];

                        $_SESSION['id'] = $row['id'];

                        header("Location: user_auth");

                        exit();

                    } else {

                        header("Location: user_auth?error=Incorrect User mail or password");

                        exit();

                    }

                } else {

                    header("Location: user_auth?error=Incorrect User mail or password");

                    exit();

                }

            }

        } else {

            header("Location: user_auth");

            exit();

        }
}*/

function getList(){
    $pdo = dbConnection();
    $order_by = 'ORDER BY id DESC';
    if(!empty($filter = $_GET['filter'])){
        if(!empty($type = $_GET['type'])){
            $order_by = 'ORDER BY '.$filter.' '.$type;
        }
    }
    $listing = $pdo->query("SELECT * FROM Article ".$order_by);
    return $listing->fetchAll();
}
if(!empty($_POST['action'])){

    $action = $_POST['action'];
    if($action == 'save_blog'){
        $save = 'INSERT INTO article(title, subtitle, content, published_on, updated_on) VALUES(:title, :subtitle, :content, :published_on, :updated_on)';
        $statement = $pdo->prepare($save);
        $statement->execute([
            ':title' => $_POST['title'],
            ':subtitle' => $_POST['subtitle'],
            ':content' => $_POST['content'],
            ':published_on' => date('Y-M-d H:i:s'),
            ':updated_on' => date('Y-M-d H:i:s')
        ]);

        $publisher_id = $pdo->lastInsertId();

        echo 'The blog post id ' . $publisher_id . ' has been inserted';
    }
    elseif($action == 'edit_blog'){
        if(!empty($id = $_GET['id'])) {
            $data = [
                'title' => $_POST['title'],
                'subtitle' => $_POST['subtitle'],
                'content' => $_POST['content'],
                'updated_on' => date('Y-M-d H:i:s'),
                'id' => (int) $id
            ];
            $sql = "UPDATE article SET title=:title, subtitle=:subtitle, content=:content, updated_on=:updated_on WHERE id=:id";
            $stmt= $pdo->prepare($sql);
            $stmt->execute($data);

            echo 'The blog post id ' . $id . ' has been edited';
        }
    }
    elseif ($action == 'delete_blog'){
        if (!empty($id = $_GET['id'])){
            $dataBis = [
                'id' => (int) $id
            ];
            $delete = "DELETE FROM article WHERE id=:id";
            $prepare = $pdo->prepare($delete)->execute($dataBis);
            return true;
        }
    }
}
?>

<header>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container">
                <div class="collapse navbar-collapse g-2" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item" style="padding-right: 10px">
                            <a class="btn btn-info" id="listing" href="http://jules.local/?page=new_listing">Articles</a>
                        </li>
                        <li class="nav-item" style="padding-right: 10px">
                            <a class="btn btn-primary" id="article" href="http://jules.local/?page=new_article">Write ✍️</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-success" id="signin" href="http://jules.local/?page=user_auth">Sign in</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <?php if($_GET['page'] == 'new_article'){
     ?>
        <div class="container">
            <form method="POST" name="save_blog">
                <div class="input-group" style="display: flex">
                    <div style="padding-right: 10px">
                        <input class="form-control" type="text" name="title" placeholder="Title"/>
                    </div>
                    <div>
                        <input class="form-control" type="text" name="subtitle" placeholder="Subtitle"/>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <textarea id="editorjs" autocomplete="on" class="form-control" name="content" aria-label="Content" placeholder="Write what you want to gossip about here"></textarea>
                </div>
                <button class="btn btn-success" type="submit" value="save_blog" name="action">Save</button>
            </form>
        </div>
    <?php
    }
    ?>

    <?php if($_GET['page'] == 'edit_page'){
            $article = getArticleById($_GET['id']);
        ?>
        <div class="container">
            <form method="post" name="edit_blog">
                <div class="input-group" style="display: flex">
                    <div style="padding-right: 10px">
                        <input class="form-control" autocomplete="on" type="text" name="title" placeholder="Title" value="<?php echo $article['title']; ?>" />
                    </div>
                    <div>
                        <input class="form-control" autocomplete="on" type="text" name="subtitle" placeholder="Subtitle" value="<?php echo $article['subtitle']; ?>" />
                    </div>
                </div>
                <div class="input-group mb-3">
                    <textarea id="editorjs" autocomplete="on" class="form-control" name="content" aria-label="Content" placeholder="Write what you want to gossip about here"><?php echo $article['content']; ?></textarea>
                </div>
                <button class='btn btn-warning' role='button' value="edit_blog" name="action" type='submit'>Edit</button>
            </form>
        </div>
        <?php
        }
        ?>

        <div id="delete_modal" class="modal">
            <div class="container modal-content">
                <form method="post">
                    <h2>Do you really want to delete this post?<br></h2>
                    <a class="btn btn-outline-primary" role="button" type="submit" href="http://jules.local/?page=new_listing">Cancel</a>
                    <button class="btn btn-danger delete-row-action" role="button" value="delete_blog" name="action" type="submit">Delete</button>
                </form>
            </div>
        </div>

    <?php if($_GET['page'] == 'new_listing' || empty($_GET['page'])) {
            $blogs = getList();
    ?>
    <div class="container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#
                        <div class="btn-group-vertical btn-group-sm" role="group">
                            <a class="btn btn-outline-primary" href="http://jules.local/?page=new_listing&type=ASC&filter=id"><small>ASC</small></a>
                            <a class="btn btn-outline-danger" href="http://jules.local/?page=new_listing&type=DESC&filter=id"><small>DESC</small></a>
                        </div>
                    </th>
                    <th scope="col">Title
                        <div class="btn-group-vertical btn-group-sm" role="group">
                            <a class="btn btn-outline-primary" href="http://jules.local/?page=new_listing&type=ASC&filter=title"><small>ASC</small></a>
                            <a class="btn btn-outline-danger" href="http://jules.local/?page=new_listing&type=DESC&filter=title"><small>DESC</small></a>
                        </div>
                    </th>
                    <th scope="col">Subtitle
                        <div class="btn-group-vertical btn-group-sm" role="group">
                            <a class="btn btn-outline-primary" href="http://jules.local/?page=new_listing&type=ASC&filter=subtitle"><small>ASC</small></a>
                            <a class="btn btn-outline-danger" href="http://jules.local/?page=new_listing&type=DESC&filter=subtitle"><small>DESC</small></a>
                        </div>
                    </th>
                    <th scope="col">Publication
                        <div class="btn-group-vertical btn-group-sm" role="group">
                            <a class="btn btn-outline-primary" href="http://jules.local/?page=new_listing&type=DESC&filter=published_on"><small>Récent</small></a>
                            <a class="btn btn-outline-danger" href="http://jules.local/?page=new_listing&type=ASC&filter=published_on"><small>Ancien</small></a>
                        </div>
                    </th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>

            <?php

            foreach ($blogs as $row){

                    $id = $row["id"];
                    $title = $row["title"];
                    $subtitle = $row["subtitle"];
                    $createdOn = $row["published_on"];

                    echo '<tbody class="table_row" id="'. $id .'"><tr><th scope="row">' . $id . '</th><td>' . $title . '</td><td>' . $subtitle . '</td><td>' . $createdOn . '</td><td><div class="btn-group" role="group"><a class="btn btn-warning" role="button" type="submit" href="http://jules.local/?page=edit_page&id='. $id .'">Edit</a><a class="btn btn-danger delete_row" data-id="'. $id .'">Delete</a></div></td></tr></tbody>';
            }
        echo "</table>";
        }
        ?>
    </div>

    <?php if($_GET['page'] == 'user_auth'){
        //signIn();
    ?>
        <div class="container">
            <form method="post" name="user_auth">
                <div class="input-group" style="display: flex">
                    <div class="form-group" style="padding-right: 10px">
                        <input class="form-control" autocomplete="on" type="email" name="email" placeholder="email@exemple.fr" value="" />
                    </div>
                    <div class="form-group">
                        <input class="form-control" autocomplete="on" type="password" name="password" placeholder="password" value="" />
                    </div>
                </div>
                <button class='btn btn-success' role='button' value="user_auth" name="action" type='submit'>Login</button>
                <p>First time visiting? <a href="http://jules.local/?page=acc_create">Sign up</a></p>
            </form>
        </div>

    <?php } ?>

    <?php if ($_GET['page'] == 'acc_create'){
    ?>
        <div class="container">
            <form method="post" name="create_acc">
                <div class="input-group" style="display: flex">
                    <div class="form-group">
                        <input class="form-control" autocomplete="on" type="text" name="name" placeholder="Name" value="" />
                    </div>
                    <div class="form-group">
                        <input class="form-control" autocomplete="on" type="text" name="username" placeholder="username" value="" />
                    </div>
                    <div class="form-group" style="padding-right: 10px">
                        <input class="form-control" autocomplete="on" type="email" name="email" placeholder="email@exemple.fr" value="" />
                    </div>
                    <div class="form-group">
                        <input class="form-control" autocomplete="on" type="password" name="password" placeholder="password" value="" />
                    </div>
                    <div class="form-group">
                        <input class="form-control" autocomplete="on" type="password" name="passwordRepeat" placeholder="Confirm password" value="" />
                    </div>
                </div>
                <button class="btn btn-success" role="button" value="create_acc" name="action" onsubmit="<?php signUp() ?>" type="submit">Continue</button>
                <p>Already have an account? <a href="http://jules.local/?page=user_auth">Sign in</a></p>
            </form>
        </div>

    <?php
        signUp();

    }
    ?>

</header>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>

    $(document).ready(() => {
        // $("body").on('click', function (){
        //     $(".table_row").removeClass("table-active");
        // });

        $(".table_row").click(function (){
            $(".table_row").removeClass("table-active");
            $(this).addClass("table-active");
        });
    })

    $(document).ready(() => {
        $(".delete_row").click(function(){
            $(".modal").css("display", "block")
        })
    })

    $(document).ready(() => {
        $(".delete-row-action").click(function(){
            let del_id= $(".delete_row").attr('data-id');
            let me = $(".delete_row");
            $.ajax({
                data: {
                    action: "delete_blog"
                },
                type:'POST',
                url:'http://jules.local/?id='+del_id,
                success:function() {
                    location.reload();
                    me.closest('tbody').fadeOut();
                }
            })
        })
    })
</script>

<script>
    tinymce.init({
        selector: '#editorjs',
        plugins: [
            'a11ychecker','advlist','advcode','advtable','autolink','checklist','export',
            'lists','link','image','charmap','preview','anchor','searchreplace','visualblocks',
            'powerpaste','fullscreen','formatpainter','insertdatetime','media','table','help','wordcount'
        ],
        toolbar: 'undo redo | formatpainter casechange blocks | bold italic backcolor | ' +
            'alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help'
    });
</script>

</body>
</html>