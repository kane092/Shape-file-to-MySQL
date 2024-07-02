(function() {
	"use strict";

	window.addEventListener(
		"load",
		function() {
			// Fetch all the forms we want to apply custom Bootstrap validation styles to
			var forms = document.getElementsByClassName("needs-validation");

			// Loop over them and prevent submission
			var validation = Array.prototype.filter.call(forms, function(form) {
				form.addEventListener(
					"submit",
					function(event) {
						if (form.checkValidity() === false) {
							event.preventDefault();
							event.stopPropagation();
						}
						form.classList.add("was-validated");
					},
					false
				);
			});
		},
		false
	);
})();

$("#install_form").on("submit", function(e) {
	$("#submitID").prop("disabled", true);
	$("#submitID").val("Processing …");
	e.preventDefault();
	var actionurl = e.currentTarget.action;
	var formid = e.currentTarget.id;
	$.ajax({
		url: actionurl,
		method: "POST",
		data: $(this).serialize(),
		dataType: "json",
		success: function(data) {
			$("#submitID").prop("disabled", false);
			$("#submitID").val("Continue");
			if (data.success == false) {
				$(".alert").show();
				$("#msg").html(data.msg);
			} else if (data.success == true) {
				$("#install_form").hide();
				$("#success_install").show();
			}
		},
		error: function(data) {
			$(".alert").show();
			var msg =
				"There is an error in posting your form. Please reload and try again";
			$("#msg").html(msg);
			$("#submitID").prop("disabled", false);
			$("#submitID").val("Continue");
		}
	});
});
