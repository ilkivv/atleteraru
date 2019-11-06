<?

if(isset($_GET['version'])){
	
	if($_GET['version'] == 'full'){
		$_SESSION['VERSION_SITE'] = 'full';
	}else{
		$_SESSION['VERSION_SITE'] = 'adaptive';
	}
	
}

$full_version = isset($_SESSION['VERSION_SITE']) && $_SESSION['VERSION_SITE'] == 'full';

?>

<?if(!$full_version):?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?else:?>	
<meta name="viewport" content="width=1200, initial-scale=1">
<?endif?>