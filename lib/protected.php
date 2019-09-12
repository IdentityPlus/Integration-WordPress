<?php
	if (!defined('Identity +')){
		error_log("File \"".__FILE__."\" was called directly. ");
		exit; // Exit if accessed directly
	}
	use identity_plus\api\Identity_Plus_Utils;
?><!DOCTYPE html><HTML>
<HEAD>
	<meta name=viewport content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<link href='https://fonts.googleapis.com/css?family=Roboto+Mono|Roboto:400,100,300|Roboto+Condensed:400,300' rel='stylesheet' type='text/css'>
	<link href='<?php echo plugins_url( 'idp.css', __FILE__ ) ?>' rel='stylesheet' type='text/css'>
	<title>Identity + Protected Resource</title>
	<style>
		#bg{
			width:100%; height:100vh; background:#FAfAfA;
			background: #feffff; /* Old browsers */
			background: -moz-linear-gradient(top,  #feffff 0%, #ededed 100%); /* FF3.6-15 */
			background: -webkit-linear-gradient(top,  #feffff 0%,#ededed 100%); /* Chrome10-25,Safari5.1-6 */
			background: linear-gradient(to bottom,  #feffff 0%,#ededed 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#feffff', endColorstr='#ededed',GradientType=0 ); /* IE6-9 */
		}
		#bg h1{font-family:'Roboto Condensed'; margin-top:0px; border-bottom:1px solid #8AAC1A;}
		#bg p{font-size:18px; text-align:left;}
		#bg div{
			display:inline-block; 
			background:url('<?php echo plugins_url('img/identity-plus-shield.svg', __FILE__ ) ?>') no-repeat;
			background-size:250px; 
            background-position: left 50px;
			height:300px; 
			width:600px; 
			max-width:100%;
			max-width:90%; 
			padding-left:250px;
		}
		#bg p a{
			max-width:250px; 
			display:inline-block; 
			line-height:100%; 
			font-size:22px; 
			font-family:'Roboto Condensed'; 
			margin-right:40px;
            text-decoration:none;
		}

		#bg p a span{
			font-size:75%; 
			font-weight:300; 
			color:#808080;
		}
		@media screen and (max-width: 800px){
			#bg div{
				background-position:  center 40px;
				width:100%;
				text-align:center;	
				padding:0;
			}
			#bg div h1{
				padding-top:0px;
				text-align:center;
				width:100%;
				border-bottom:1px solid #8AAC1A;
			}
            #bg div{
                padding-top:300px;
            }
		}
	</style>
</HEAD>
<body>
		<table id="bg"><tr><td valign="middle" align="center">
				<div>
						<H1>This Resource Is Protected!</H1>
						<p>
							Acess to "<?php echo Identity_Plus_Utils::here(); ?>" is restricted.<br>
							In order to access it, you need to have the correct identityplus
							SSL Client Certificate installed in your browser.
						</p>
						<p>
							If you are the rightful owner of this resource, please connect this device to your identityplus
							account. If you are not the rightful owner but you require access to this resource please contact the owner of the site.
						</p>
						<p>
							<a href="https://get.identity.plus" target="_blank" class="button_ab">Certify My Device</a> or 
							<a href="https://identity.plus" target="_blank" style="width:initial; max-width:initial; font-size:16px; font-weight:300; font-family:'Roboto'; margin-top:20px; margin-left:30px;">Find out how it works on Identity +</a>
						</p>
				</div>
		</td></tr></table>
</body>
</HTML>
