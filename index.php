<?php 
require __DIR__ . '/wrStartup.php';
?>

<!DOCTYPE html>
<html>
<head>
	<title>Migrate ACF 2 Jet</title>
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<meta charset="utf-8">
	<meta name="author" content="Wikired Argentina">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"	rel="stylesheet" type="text/css" >
	<link href="https://cdn.wikired.com.ar/dropzone/v593/min/dropzone.min.css" rel="stylesheet" type="text/css" />
	<script src="https://cdn.wikired.com.ar/dropzone/v593/min/dropzone.min.js"></script>
</head>
<body class="bg-primary-subtle">
	<main class="container-fluid  "">
		<h1 class="text-center">Migrate ACF to Jet Engine</h1>
		<br>

		<!-- <form> -->
			<div class="row  mb-3" >
				<!-- <div class="col-2 wrCol offset-1  text-primary-emphasis bg-primary-subtle">
					<span class="wrLabel">ACF Json File</span>
				</div> -->
				<div class="col-8 offset-2">
					<div class="dropzone" id="myDropzone">
				</div>
			</div>
		<!-- </form> -->
	</main>
</body>
<footer class="js scripts">
	<script src="https://cdn.wikired.com.ar/jquery/jquery.min.js"></script>
	<!-- <script src="https://cdn.wikired.com.ar/underscore/underscore-min.js"></script> -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">	</script>
	<!-- <script src="https://kit.fontawesome.com/51c486d49e.js" crossorigin="anonymous"></script> -->
	<!-- <script src="https://cdn.wikired.com.ar/utils/jquery.blockUI.js"></script> -->


	<script  type="text/javascript">

		Dropzone.options.myDropzone = {
			url: "./upload.php",
			autoProcessQueue: true,
			paramName: "file",
			clickable: true,
			maxFilesize: 5, //in mb
			addRemoveLinks: false,
			uploadMultiple: false,
			// disablePreviews: true,
			acceptedFiles: '.json',
			dictDefaultMessage: "Upload the ACF json  file here",
			init: function() {
				// this.on("sending", function(file, xhr, formData) {
				// 	console.log("sending file");
				// });
				this.on("success", function(file, rv) {
					//console.log('ready for download');
					if (rv.status == 'ok') {
						downLoad(rv);
					} else {
						alert(rv.msg);
					}
					
				});
				// this.on("addedfile", function(file){
				// 	console.log('file added');
				// });
			}
		};

		function downLoad(params){
			console.info('params');
			console.log(params);
			// make form
			let form = document.createElement("form");
			form.setAttribute("method", "post");
			form.setAttribute("action", './download.php');
			form.setAttribute("target", '_blank');

			let input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'jetFileName';
			input.value = params.jetFileName;
			form.appendChild(input);


			document.body.appendChild(form);
			form.submit();
			document.body.removeChild(form);
		}

		// Jquery document.ready
		$(function() {
		})



		function _log($msg , $title = false , $time = false){
			if ($title){
				console.info( '*** ' + $title + ' ------------------------');
			}
			if ($time){
				const $ahora = new Date().getTime() / 1000;
				console.log( '*** ' + $ahora); 
			}
			if ( typeof $msg == 'boolean'){
				$msg = $msg.toString();
			}
			console.log($msg);    
		}

		function _debug($msg , $title = false , $time = false){
			_log($msg , $title  , $time);
		}
	</script>
</footer>
</html>

<?php 

// SOLO PARA DEBUG !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


// _debug( $network['CRP']['sites'] , 'network');
// _debug( $siteMatrix , 'sitematrix');
//_debug( $network , 'network');

// $wpconfig = getWpConfig($network);

// _debug( $wpconfig , 'wpConfig');


// logWrite('network',$network);
// logWrite('siteMatrix',$siteMatrix);


