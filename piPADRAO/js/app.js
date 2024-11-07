var btnSignin = document.querySelector("#signin");
var btnSignup = document.querySelector("#signup");
var Body = document.querySelector("body");
/*identificar como varialvel as classes q vamos usar*/


btnSignin.addEventListener("click", function () {
    Body.className = "sign-in-js";
});
/*adicionar um evento de click para quando usarmos o botao mudar a varialvel do body*/

btnSignup.addEventListener("click", function () {
    Body.className = "sign-up-js";
});