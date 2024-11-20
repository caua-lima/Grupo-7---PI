<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" 
    integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM5m8j8Rx5A1v0o+W9e9FqK8e+h5W5m5t7t5H" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


</head>

<body>
  <div class="container">

        <div class="content first-content">
            <!-- PRIMEIRA COLUNA(MOVIMENTA) -->
            <div class="first-column">
                <h2 class="title title-primary" >Bem vindo Professor!</h2>
                <p class="description description-primary">Para se conectar com a gente</p>
                <p class="description description-primary">Faca seu cadrastro aqui</p>
                <button id="signin" class="btn btn-primary">Cadastrar-se</button>
            </div>

            <!-- Fim movimento -->
            <!-- SEGUNDA COLUNA FORMULARIO -->
            <div class="second-column">              
            <h2 class="title title-second">Conectar</h2>
            <form action="login.php" class="form" method="post">
              
               
                <label class="label-input " for="">
                    <i class="fa-regular fa-envelope icon-modify"></i>
                    <input type="email" placeholder="Email" name="email">
                </label>
                
              
                <label class="label-input" for="">
                    <i class="fa-solid fa-lock icon-modify"></i>
                    <input type="password" placeholder="Senha" name="senha">
                </label>
                <button class="btn btn-professor">Logar</a>
                <!-- Fim formulario professor -->
            </form>
            </div>
            </div>




        <div class="content second-content">
        <!-- PRIMEIRA COLUNA(MOVIMENTA) -->
        <div class="first-column">
                <h2 class="title title-primary">Ola, Professor</h2>
                <p class="description  description-primary">Para se conectar com a gente</p>
                <p class="description  description-primary">Por favor logue por aqui!</p>
                <button id="signup" class="btn btn-primary">Logar</button>
            </div>
            <!-- Fim movimento -->
            <!-- SEGUNDA COLUNA FORMULARIO -->
            <div class="second-column">
                <h2 class="title title-second">Cadrastre-se</h2>
            <form action="cadastrar_usuarios.php" method="post" class="form" >
                <label class="label-input" for="">
                    <i class="fa-regular fa-user icon-modify"></i>
                    <input type="text" placeholder="Nome Completo" name="nome"> 
                </label>
               
                <label class="label-input " for="">
                    <i class="fa-regular fa-envelope icon-modify"></i>
                    <input type="email" placeholder="Email" name="email">
                </label>
                
                <label class="label-input" for="">
                    <i class="fa-solid fa-user-secret icon-modify" ></i>
                    <input type="text" placeholder="Matricula" name="matricula">
                </label>
              
                <label class="label-input" for="">
                    <i class="fa-solid fa-lock icon-modify"></i>
                    <input type="password" placeholder="Senha" name="senha">
                </label>
                <button class="btn btn-cordenador">Cadastrar-se</button>
                <!-- Fim formulario cordenador -->
            </form>

            </div>
         </div>

    </div>
    <script src="js/app.js"></script>
</body>
</html>
