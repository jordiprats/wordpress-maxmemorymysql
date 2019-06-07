<?php
/**
 * @package mysqlmaxmemory
 * @version 1.0
 */
/*
Plugin Name: mysqlmaxmemory
Plugin URI: http://systemadmin.es
Description: Calculem el maxim de memoria d'un MySQL a partir de un my.cnf
Author: Jordi Prats
Version: 1.0
Author URI: http://systemadmin.es
*/

function mysqlmaxmemory_share_result()
{
	if(isset($_REQUEST['mysqlmaxmemory_mycnf']))
	{	
                $inver="";

                $invar=array(
                        "key_buffer_size" => -1,
                        "query_cache_size" => -1,
                        "tmp_table_size" => -1,
                        "innodb_buffer_pool_size" => -1,
                        "innodb_additional_mem_pool_size" => 0,
                        "innodb_log_buffer_size" => -1,
                        //
                        "max_connections" => -1,
                        "sort_buffer_size" => -1,
                        "read_buffer_size" => -1,
                        "read_rnd_buffer_size" => -1,
                        "join_buffer_size" => -1,
                        "thread_stack" => -1,
                        "binlog_cache_size" => -1
                );

                preg_match("/[^_a-z]version[^_a-z]+[^\n]*\n/",$_REQUEST['mysqlmaxmemory_mycnf'],$matchver);
                preg_match("/[0-9]+\.[0-9]+\.[0-9]+/",$matchver[0],$inver);

                $inver=$inver[0];

                //echo "==".$inver."==\n";

                foreach ($invar as $key => $value)
                {
                        //echo "== ".$key."\n";
                        //preg_match("/".$key."[^\n]*\n/",$_REQUEST['mysqlmaxmemory_mycnf'],$matches,PREG_OFFSET_CAPTURE);
                        preg_match("/\b".$key."[^\n]*\n/",$_REQUEST['mysqlmaxmemory_mycnf'],$matches);
                        //print_r($matches);

                        if(isset($matches[0]))
                        {
                                preg_match("/[0-9]+[KMGkmg]?/",$matches[0],$match);
                                //print_r($match);
                                $invar[$key]=$match[0];
                        }
                }

                //echo "<pre>";
                //print_r($invar);
                //echo "</pre>";

                $readytogo=1;

                foreach ($invar as $key => $value)
                {
                        if($value==-1) $readytogo=0;
                }

                if(!is_array($outvar))
                {
                        $outvar=array(
                                0 => -1,
                                1 => -1,
                                2 => -1,
                                3 => -1,
                                4 => -1,
                                5 => -1,
                                6 => -1,
                                7 => -1,
                                8 => -1,
                                9 => -1,
                                10 => -1,
                                11 => -1,
                                12 => -1
                        );
                        $c=0;
                        foreach ($invar as $key => $value)
                        {
                                //echo $key." --> ".$value."<br>";
                                $outvar[$c]=$value;
                                $c++;
                        }
                }


                if($readytogo)
                {
		
			$hashinstance=str_rot13(strrev(rtrim(strtr(base64_encode(gzcompress(json_encode($outvar),9)), '+/', '-_'), '=')));	
			if( (!isset($_REQUEST['mysqlmaxmemory_url'])) && (strlen(get_permalink()."?mysqlmaxmemory_url=".$hashinstance)<250) )
			{
				exit( wp_redirect( get_permalink()."?mysqlmaxmemory_url=".$hashinstance ));
			}
		}
	}
}

add_action( 'template_redirect', 'mysqlmaxmemory_share_result' );


function mysqlmaxmemory_numberToHuman($num)
{
	$kB=floor($num/1024);
	if($kB<1) return ""+floatval($num);
	$mB=floor($num/1024/1024);
	if($mB<1) return floatval(number_format($num/1024,2))."K";
	$gB=floor($num/1024/1024/1024);	
	if($gB<1) return floatval(number_format($num/1024/1024,2))."M";
	return floatval(number_format($num/1024/1024/1024,2))."G";
}


function mysqlmaxmemory_printform()
{
	//$lang=get_bloginfo("language");
	?>
	
	<p>Mediante esta herramienta puedes calcular el <strong>consumo maximo de RAM</strong> que puede llegar a usar un <strong>servidor de bases de datos MySQL</strong>. Para ello es necesario leer las variables que definen el tamaño de los buffers.</p>	
	
	<p>Copia el resultado del siguiente comando:
	<strong>SHOW VARIABLES;</strong>
	</p>
	
	<form method=POST>
	<textarea             id="s"            name="mysqlmaxmemory_mycnf" style="width: 100%" rows=10></textarea>
	<input    type=submit id="searchsubmit" value="enviar">
	</form>

	<span class="alignright">
    	<!-- g:plusone size="medium" href="<?php echo get_permalink(); ?>"></g:plusone -->
	<!-- -->
    	<a href="https://twitter.com/share?url=<?php echo get_permalink(); ?>" class="twitter-share-button" data-lang="en">Tweet</a>
    	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	</span>


	<?php
}


function mysqlmaxmemory($atts)
{

        if(!( (isset($_REQUEST['mysqlmaxmemory_mycnf'])) || (isset($_REQUEST['mysqlmaxmemory_url'])) ))
	{
		//FORM
		mysqlmaxmemory_printform();
	}
	else
	{
		//WORK
	
	
		$invar=array(
			"key_buffer_size" => -1,
			"query_cache_size" => -1,
			"tmp_table_size" => -1,
			"innodb_buffer_pool_size" => -1,
			"innodb_additional_mem_pool_size" => 0,
			"innodb_log_buffer_size" => -1,
			//
			"max_connections" => -1,
			"sort_buffer_size" => -1,
			"read_buffer_size" => -1,
			"read_rnd_buffer_size" => -1,
			"join_buffer_size" => -1,
			"thread_stack" => -1,
			"binlog_cache_size" => -1
		);
	
		if (!isset($_REQUEST['mysqlmaxmemory_url']))
		{	
			$inver="";

			preg_match("/[^_a-z]version[^_a-z]+[^\n]*\n/",$_REQUEST['mysqlmaxmemory_mycnf'],$matchver);
			preg_match("/[0-9]+\.[0-9]+\.[0-9]+/",$matchver[0],$inver);

			$inver=$inver[0];

			//echo "==".$inver."==\n";

			foreach ($invar as $key => $value)
			{
				//echo "== ".$key."\n";
				//preg_match("/".$key."[^\n]*\n/",$_REQUEST['mysqlmaxmemory_mycnf'],$matches,PREG_OFFSET_CAPTURE);
				preg_match("/\b".$key."[^\n]*\n/",$_REQUEST['mysqlmaxmemory_mycnf'],$matches);
				//print_r($matches);

				if(isset($matches[0]))
				{
					preg_match("/[0-9]+[KMGkmg]?/",$matches[0],$match);
					//print_r($match);
					$invar[$key]=$match[0];
				}
			}
		
			//echo "<pre>";
			//print_r($invar);	
			//echo "</pre>";

			$readytogo=1;

			foreach ($invar as $key => $value)
			{
				if($value==-1) $readytogo=0;	
			}
		}
		else
		{
			$outvar=json_decode(gzuncompress(base64_decode(strtr(strrev(str_rot13($_REQUEST['mysqlmaxmemory_url'])),'-_', '+/'))),true);

                        $c=0;
			$readytogo=1;
                        foreach ($invar as $key => $value)
                        {
				if(!isset($outvar[$c])) $readytogo=0;		
				$invar[$key]=$outvar[$c];
                                $c++;
                        }
		}
	
		if(!is_array($outvar))
		{
	                $outvar=array(
				0 => -1,
				1 => -1,
				2 => -1,
				3 => -1,
				4 => -1,
				5 => -1,
				6 => -1,
				7 => -1,
				8 => -1,
				9 => -1,
				10 => -1,
				11 => -1,
				12 => -1
			);
			$c=0;
			foreach ($invar as $key => $value)
			{
				//echo $key." --> ".$value."<br>";
				if(!is_int($value)) $readytogo=0;
				$outvar[$c]=$value;
				$c++;   
			}
		}


		if($readytogo)
		{	
				
			$servermem=
					$invar["key_buffer_size"]+
					$invar["query_cache_size"]+
					$invar["tmp_table_size"]+
					$invar["innodb_buffer_pool_size"]+
					$invar["innodb_additional_mem_pool_size"]+
					$invar["innodb_log_buffer_size"];
	
			$perthreadmem=
					$invar["sort_buffer_size"]+
					$invar["read_buffer_size"]+
					$invar["read_rnd_buffer_size"]+
					$invar["join_buffer_size"]+
					$invar["thread_stack"]+
					$invar["binlog_cache_size"];
	
			$maxmysqlmem = $servermem + $invar["max_connections"]*$perthreadmem;
			
			//echo "<p>El servidor <strong>MySQL</strong> puede llegar a consumir un total de <strong>".mysqlmaxmemory_numberToHuman($maxmysqlmem)."</strong></p>";
			echo "<p>El servidor <strong>MySQL</strong> puede llegar a consumir un total de:</p>";
			
			echo mysqlmaxmemory_numberToHuman($servermem)."+(".mysqlmaxmemory_numberToHuman($invar["max_connections"])."*".mysqlmaxmemory_numberToHuman($perthreadmem).") = <strong>".mysqlmaxmemory_numberToHuman($maxmysqlmem)."</strong></p>";
			//echo "<ul>";
			//echo "<li>Consumo maximo base: <strong>".mysqlmaxmemory_numberToHuman($servermem)."</strong></li>";
			//echo "<li>Consumo por thread (<strong>".$invar["max_connections"]."</strong> configurados): <strong>".mysqlmaxmemory_numberToHuman($perthreadmem)."</strong></li>";
			//echo "</ul>";
			//echo "Server-wide: ".mysqlmaxmemory_numberToHuman($servermem)." + "
			//	.$invar["max_connections"]." threads * "
			//	.mysqlmaxmemory_numberToHuman($perthreadmem)."\n";
			//echo "<p>Por lo tanto el consumo maximo es: <strong>".mysqlmaxmemory_numberToHuman($maxmysqlmem)."</strong></p>\n";
			//
			//
			//echo "<script type=\"text/javascript\">_gaq.push(['_trackEvent', 'about', 'linkedin', 'click']);</script>";
			?>
			<p>El resultado desglosado para los buffers compartidos (<?=mysqlmaxmemory_numberToHuman($servermem)?>)</p>
			<ul>
				<li><strong>key_buffer_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["key_buffer_size"])?></li>
				<li><strong>query_cache_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["query_cache_size"])?></li>
				<li><strong>tmp_table_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["tmp_table_size"])?></li>
				<li><strong>innodb_buffer_pool_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["innodb_buffer_pool_size"])?></li>
				<li><strong>innodb_additional_mem_pool_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["innodb_additional_mem_pool_size"])?></li>
				<li><strong>innodb_log_buffer_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["innodb_log_buffer_size"])?></li>
			</ul>

			<p>Mientras que las siguientes son por thread (<?=floatval($invar["max_connections"])?> configurados), suman un total de <?=mysqlmaxmemory_numberToHuman($perthreadmem*$invar["max_connections"])?> (<?=mysqlmaxmemory_numberToHuman($perthreadmem)?> por thread):</p>

			<ul>
				<li><strong>sort_buffer_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["sort_buffer_size"])?></li>
				<li><strong>read_buffer_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["read_buffer_size"])?></li>
				<li><strong>read_rnd_buffer_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["read_rnd_buffer_size"])?></li>
				<li><strong>join_buffer_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["join_buffer_size"])?></li>
				<li><strong>thread_stack</strong>: <?=mysqlmaxmemory_numberToHuman($invar["thread_stack"])?></li>
				<li><strong>binlog_cache_size</strong>: <?=mysqlmaxmemory_numberToHuman($invar["binlog_cache_size"])?></li>
			</ul>
			
	        	<span class="alignright">
        		<!-- g:plusone size="medium" href="<?php echo get_permalink(); ?>"></g:plusone -->
        		<!-- -->
		        <a href="https://twitter.com/share?url=<?php echo get_permalink(); ?>" class="twitter-share-button" data-lang="en">Tweet</a>
		        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		        </span>
			<?php
				
			//print_r($outvar);	
			//echo json_encode($invar);
			//echo "==<br>";
			//echo json_encode($outvar);
			//echo base64_encode(gzcompress(json_encode($outvar),9));
			//rtrim(strtr(base64_encode($data), '+/', '-_'), '=')
			//$hashinstance=str_rot13(strrev(rtrim(strtr(base64_encode(gzcompress(json_encode($outvar),9)), '+/', '-_'), '=')));
			//echo "<pre>".$hashinstance."</pre>";
			//echo "strlen: ".strlen(get_permalink()."?".$hashinstance)."<br>";
			//echo "<br>";
			//print_r(json_decode(gzuncompress(base64_decode(strtr(strrev(str_rot13($hashinstance)),'-_', '+/'))),true));
			//echo print_r(json_encode($invar));	

		}
		else
		{
			echo "<p>No se han encontrado variables suficientes para calcular el consumo de memoria, asegurate de copiar todo el resultado de <strong>SHOW VARIABLES;</strong></p>";
		}
	}
}

add_shortcode('show_mysqlmaxmemory', 'mysqlmaxmemory');

?>
