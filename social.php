<?php
            session_start();

            $loggedIn = false;

            if(isset($_SESSION['loggedIn']) && isset($_SESSION['name'])) {
                $loggedIn = true;
            }

            $conn = new  mysqli('192.168.64.2',  'root',  '',  'social1');

            function createCommentRow ($data)
            {return '
                        <div class="comment">
                                <div class="user">'.$data['name'].'<span class="time">'.$data['createdOn'].'</span></div>
                                <div class="userComment">'.$data['comment'].'</div>
                                <div class="reply"><a href="javascript:void(0)" onclick="reply(this)">Leave a Message</a></div>
                                <div class="replies">
                                    <!--
                                    <div class="comment">
                                        <div class="user">Oakley <span class="time">2021-03-09</span></div>
                                        <div class="userComments">this is a good job</div>
                                    </div>
                                    -->
                                </div>
                            </div>
                    ';
            } 

        if (isset($_POST['getAllComments'])) {
            $start = $conn->real_escape_string($_POST['start']);
            $response = "";
            $sql = $conn->query("SELECT name, comment, DATE_FORMAT(comments.createdOn, '%Y-%m-%d  %H:%i:%S') AS createdOn FROM comments INNER JOIN users ON comments.userID = users.id ORDER BY comments.id DESC LIMIT $start, 20");
            while($data = $sql->fetch_assoc())
                $response  .= createCommentRow($data);

            exit($response);
        }
        if (isset($_POST['addComment'])) {
            $comment = $conn->real_escape_string($_POST['comment']);

            $conn->query("INSERT INTO comments (userID, comment, createdOn) VALUES ('".$_SESSION['userID']."','$comment',NOW())");
            $sql = $conn->query("SELECT name, comment, DATE_FORMAT(comments.createdOn, '%Y-%m-%d  %H:%i:%S') AS createdOn FROM comments INNER JOIN users ON comments.userID = users.id ORDER BY comments.id DESC LIMIT 1");
            $data = $sql->fetch_assoc();
            exit(createCommentRow($data));
        }
        if (isset($_POST['register'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);

        if (filter_var($email, FILTER_VALIDATE_EMAIL))  {
            $sql = $conn->query( "SELECT id FROM users WHERE email='$email'");
            if ($sql->num_rows > 0)
                exit('failedUserExists');
            else {
                $ePassword = password_hash($password, PASSWORD_BCRYPT);
                $conn->query("insert into users (name, email , password, createdOn) VALUES ('$name', '$email', '$ePassword', NOW())");

                $sql = $conn->query("SELECT id FROM users ORDER BY id DESC LIMIT 1");
                $data = $sql->fetch_assoc();

                $_SESSION['loggedIn'] = 1 ;
                $_SESSION['name'] = $name ;
                $_SESSION['email'] = $email ;
                $_SESSION['userID'] = $data['id'] ;

                exit('success');
             }
      }   else
            exit('failedEmail');
  }
    if (isset($_POST['logIn'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL))  {
        $sql = $conn->query( "SELECT id, password, name FROM users WHERE email='$email'");
        if ($sql->num_rows == 0)
            exit('failed');
        else {
            $data = $sql->fetch_assoc();
            $passwordHash = $data['password'];

            if(password_verify($password, $passwordHash)) {
                $_SESSION['loggedIn'] = 1;
                $_SESSION['name'] = $data['name'];
                $_SESSION['email'] = $email;
                $_SESSION['userID'] = $data['id'];

                exit('success');
            }else
                exit('failed');
        }
    }   else
            exit('failed');
    }

    $sqlNumComments = $conn->query("SELECT id FROM comments");
    $numComments = $sqlNumComments->num_rows;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name= "viewport "
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Social</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style type="text/css">

.user {
    font-weight: bold;
    color: black;
}

.header {
    color: white;
    width:750px;
    text-align: justify;
    margin-left: 150px;
    margin-top: 50px;
    margin-bottom: 50px;
}
.header span {
    display:inline-block;
    width:100%;
}

.story {
    color: white;
    width:500px;
    text-align: justify;
    margin-top: -400px;
    margin-bottom: 10px;
    margin-left: 600px;
}
.story span {
    display:inline-block;
    width:100%;
}
.result {
    margin-left: 75px;
    margin-top: 50px;
    margin-bottom: 50px;
    width:500px; 
    height:500px;
}
.bw {
    margin-left: -10px;
    margin-top: 0px;
    margin-bottom: 20px;
    position: fixed;
    bottom: 0;
}
 .top {
    margin-left: 1100px;
    margin-top: 0px;
    margin-bottom: 300px;
    position: fixed;
    bottom: 0;

 }

.s {
    color: white;
    font-size:30px;
    margin-left: 100px;
    margin-right: 200px;
    margin-top: 100px;
    margin-bottom: 10px;
}
.r {
    color: white;
    width:690px;
    text-align: justify;
    margin-left: 100px;
    margin-top: 30px;
    margin-bottom: 10px;
}
.r span {
    display:inline-block;
    width:100%;
}
.footer {
    color: #808080;
    margin: 0 auto;
    margin-top: 10px;
    margin-bottom: 10px;
    height: 10px;

}
.footer1 {
    
    margin-left: 490px;
    margin-top: 0px;
    margin-bottom: 20px;
    position: fixed;
    bottom: 0;

}

#registerModal input, #logInModal input {
margin-top: 10px ;
}



</style>


</head>
<body style=" background-color:#000000">
<div class="modal" id="registerModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registration</h5>
            </div>
            <div class="modal-body">
                <input type="text" id="userName" class="form-control" placeholder="Your Name">
                <input type="email" id="userEmail" class="form-control" placeholder="Your Email">
                <input type="password" id="userPassword" class="form-control" placeholder="Password">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="registerBtn">Register</button>
                <button class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="logInModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log In</h5>
            </div>
            <div class="modal-body">
                <input type="email" id="userLEmail" class="form-control" placeholder="Your Email">
                <input type="password" id="userLPassword" class="form-control" placeholder="Password">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="loginBtn">Log In</button>
                <button class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <div class="container" style="margin-top: 40px;">
        <div class="row">
            <div class="col-md-12" align="right">
        <?php
        if (!$loggedIn)
            echo '
                <button class="btn btn-primary" data-toggle="modal" data-target="#registerModal">Register</button>
                <button class="btn btn-success" data-toggle="modal" data-target="#logInModal">Log In</button>
            ';
        else
            echo '
                <a href="logout.php" class="btn btn-warning">Log Out</a>
            ';
        ?>
            </div>


        <?php
        if (!$loggedIn)
            echo '

                <div class="col-md-12" align="center" style="font-size:40px" >Welcome to imaginary space</div></br>
                <div class="col-md-12" align="center" style="font-size:20px" >Please click log in to come in your imaginary sky</div></br>
                <div class="col-md-12" align="center" style="font-size:20px" >If you do not have an account, please click to register</div>
                <img width="100%" height="100%" src="background.jpeg" style="position: absolute; left: 0; top: 0; z-index:-1;"/ >             
                <div class="bw"><a href="https://www.nasa.gov/" style="color:#808080;"</a>Image contributor: NASA Image Library</div>
                <div class="footer1"><a href="#" style="color:#808080;">Designed by Hao Ding</a></div>
            ';
        else
            echo '
                
              <div class="header" style="font-size:20px" >Congratulations, you have obtained a series of "imaginary starry sky".</div></br>
              <div class="header" align="left" style="font-size:20px" ><span>In fact, there are many mysteries in the universe waiting for human beings to explore. Since the birth of the universe, everything that has happened in the universe seems to be unknown to us. Because if the age of the universe is compared to 24 hours, then human existence is only the last few seconds. Now you have the opportunity to spy on the evolution of the universe. Although these pictures are not the true history of the evolution of the universe, it is a true universe for the computer of author. Next, let‘s walk into the birth of the GAN universe. . .</span></div></br>
              
                <img class="result" align="center" src="1.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="2.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="3.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="4.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="5.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="6.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="7.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="8.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="9.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="10.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="11.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="12.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="13.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="14.jpg" /><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>
                <img class="result" align="center" src="samples.gif" /></br><div class="story"><span>About 13.8 billion years ago</br></br>With the explosion of a singularity, time and space were born, and the resulting hot matter radiated in all directions at the speed of light.</span></div></br>


                <div class="s" >Source: </div>
                <div class="r"><span>Five-Year Wilkinson Microwave Anisotropy Probe (WMAP) Observations: Data Processing, Sky Maps, and Basic Results (PDF). nasa.gov. [2008-03-06].</span></div>
                <div class="r"><span>Five-Year Wilkinson Microwave Anisotropy Probe (WMAP) Observations: Data Processing, Sky Maps, and Basic Results (PDF). nasa.gov. [2008-03-06].</span></div>
                <div class="r"><span>Five-Year Wilkinson Microwave Anisotropy Probe (WMAP) Observations: Data Processing, Sky Maps, and Basic Results (PDF). nasa.gov. [2008-03-06].</span></div>
                <div class="r"><span>Five-Year Wilkinson Microwave Anisotropy Probe (WMAP) Observations: Data Processing, Sky Maps, and Basic Results (PDF). nasa.gov. [2008-03-06].</span></div>






                <HR style="FILTER: alpha(opacity=100,finishopacity=0,style=3)" width="90%" color=#808080 SIZE=3>
                 <div class="footer" align="center"><a href="#" style="color:#808080;"</a> Copyright Hao Ding; 2021 Northumbria University</div>

                <div class="top"><a href="#" style="color:#808080;"</a>⌃TOP</div>
            ';
        ?>
       
</div>
            </div>
<div></div>>

        </div>
        <!--<div class="row" style="margin-top: 20px;margin-bottom: 20px;">
            <div class="col-md-12" align="center">
                <iframe width="1200" height="800" src="http://5b0988e595225.cdn.sohucs.com/images/20180824/682fe6e714ec4bb2b6faa7006ddbf104.jpeg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
        -->
        
        
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var isReply = false, max = <?php echo $numComments ?>;

        $(document).ready(function() {
            $("#addComment, #addReply").on('click', function () {
                var comment;

                if(!isReply)
                    comment = $("#mainComment").val();
                else
                    comment = $("#replyComment").val();

                if (comment.length > 5) {
                    $.ajax({
                            url: 'social.php',
                            method: 'POST',
                            dataType: 'text',
                            data: {
                                addComment: 1,
                                comment: comment,
                                isReply: isReply
                            }, success: function (response) {
                                max++;
                                $("#numComments").text(max + " News");

                                if(!isReply) {
                                    $(".userComments").prepend(response);
                                    $("#mainComments").val("");
                                } else {
                                    $("#replyComment").val("");
                                    $(".replyRow").hide();
                                    $('.replyRow').parent().next().append(response);

                                }

                            }
                        });
                } else
                alert('Please Check Your Inputs');
            });

            $("#registerBtn").on('click', function () {
                var name = $("#userName").val() ;
                var email = $("#userEmail").val();
                var password = $("#userPassword").val();


                if (name != "" && email != "" && password != "") {
                    $.ajax({
                        url: 'social.php',
                        method: 'POST',
                        dataType: 'text',
                        data: {
                            register: 1,
                            name: name,
                            email: email,
                            password: password
                        }, success: function (response) {
                            if (response === 'failedEmail')
                                alert('Please insert valid email address!');
                            else if (response === 'failedUserExists')
                                alert('User with this email already exists!');
                            else
                                window.location = window.location;

                        }
                    });
                } else
                    alert('Please Check Your Inputs');
            });

            $("#loginBtn").on('click', function () {
                var email = $("#userLEmail").val();
                var password = $("#userLPassword").val();


                if (email != "" && password != "") {
                    $.ajax({
                        url: 'social.php',
                        method: 'POST',
                        dataType: 'text',
                        data: {
                            logIn: 1,
                            email: email,
                            password: password
                        }, success: function (response) {
                            if (response === 'failed')
                                alert('Please check your login details!');
                            else
                                window.location = window.location;
                        }
                    });
                } else
                    alert('Please Check Your Inputs');
            });

            getAllComments(0, max);
        });

        

        
    </script>
</body>
</html>