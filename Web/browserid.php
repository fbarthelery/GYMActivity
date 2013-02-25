<script  type="text/javascript" src="https://login.persona.org/include.js"></script>
<script type="text/javascript">
var signinLink = document.getElementById('browserid-login');
if (signinLink) {
  signinLink.onclick = function() { navigator.id.request(); };
}
var signoutLink = document.getElementById('logout');
if (signoutLink) {
  signoutLink.onclick = function() { navigator.id.logout(); };
}

navigator.id.watch({
    loggedInUser: <?php if (isset($_SESSION['EMAIL'])){ echo '"'.$_SESSION['EMAIL'].'"'; } else { echo 'null'; } ?>,
    onlogin: function (assertion) {
	var assertion_field = document.getElementById("assertion-field");
	assertion_field.value = assertion;
	var login_form = document.getElementById("start");
	login_form.submit();
    },
    onlogout: function () {
	window.location = 'logout.php';
    }
});
</script>
