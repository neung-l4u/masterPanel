const cmdLogin = $("#cmdLogin");
const formUser = $("#formUser");
const formPassword = $("#formPassword");
const formRemember = $("#formRemember");
const resultText = $("#resultText");

$(() => {
  cmdLogin.click(() => {
    resultText
      .empty()
      .html(
        "Authentication is loading... <img alt='Loading' src='assets/img/loading.gif'>"
      );

    const reqLogin = $.ajax({
      url: settings.url_authentication,
      method: "POST",
      async: false,
      cache: false,
      dataType: "json",
      data: {
        act: "login",
        u: formUser.val(),
        p: formPassword.val(),
        remember: formRemember.is(":checked"),
      },
    });

    reqLogin.done(function (res) {
      res.msg = undefined;
      console.log(res);
      if(res.result === "success"){
        resultText.removeClass("text-danger").addClass("text-primary")
            .empty()
            .html(
                '<i class="fa-solid fa-check text-primary"></i> <small>The authentication process succeeded.</small>'
            );
        setTimeout(() => { location.replace('chkLogin.php?act=login'); }, 500);
      }else if(res.result === "fail"){
        resultText.removeClass("text-primary").addClass("text-danger")
            .empty()
            .html(
                '<i class="fa-solid fa-x text-danger"></i> <small>'+res.msg+'</small>'
            );
      }
    });

    reqLogin.fail(function (xhr, status, error) {
      console.log("ajax get Price fail!!");
      console.log(status + ": " + error);
    });
  });
}); //ready
