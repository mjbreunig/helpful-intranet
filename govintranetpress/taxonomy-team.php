<?php
/**
 * The template for displaying team pages.
 *
 */

$directorystyle = get_option('general_intranet_staff_directory_style'); // 0 = squares, 1 = circles
$showmobile = get_option('general_intranet_show_mobile_on_staff_cards'); // 1 = show


get_header(); ?>
<div class="row">
	<div class='breadcrumbs'>
		<a href="<?php echo site_url(); ?>">Home</a>
		&raquo; <a href="<?php echo site_url(); ?>/staff-directory/">Staff directory</a>
		&raquo; <?php single_cat_title(); ?>
	</div>

<?php
	 $fulldetails=get_option('general_intranet_full_detail_staff_cards');
	 
	if ( have_posts() )
		the_post();

		$slug = pods_url_variable(-1);
		$terms = get_term_by('slug',$slug,'team',ARRAY_A); 
		$teamname = $terms['name'];
		$termid = $terms['term_id'];
		$teamparent = $terms['parent'];
		
		$taxteam = new Pod ('team', $termid);
		$teamleader = $taxteam->get_field('team_head');
		
		$alreadyshown=array();
		
?>
		<div class="col-lg-12 col-md-12 col-sm-12  white">
		<div class="col-lg-8 col-md-8 col-sm-12">
			<h1>Staff directory</h1>
			<form class="form-horizontal" role="form" id="searchform2" name="searchform2" action="<?php echo site_url( '/search-staff/' ); ?>">
			  <div class="col-lg-12 col-md-12 col-sm-12 ">
				<div id="staff-search" class="well well-sm">
						<div class="input-group">
					    	 <input type="text" class="form-control pull-left" placeholder="Search for a name, job title, skills, phone number..." name="q" id="s2" value="<?php echo $_GET['s'];?>">
							 <span class="input-group-btn">
								 <button class="btn btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i></button>
							 </span>
							<?php
						  	$terms = get_terms('team',array('hide_empty'=>false,'parent' => '0',));
							if ($terms) {
								$otherteams='';
						  		foreach ((array)$terms as $taxonomy ) {
						  		    $themeid = $taxonomy->term_id;
						  		    $themeURL= $taxonomy->slug;
						  			$otherteams.= " <li><a href='".site_url()."/team/{$themeURL}/'>".$taxonomy->name."</a></li>";
						  		}  
						  		echo "<div class='btn-group pull-right'><button type='button' class='btn btn-info dropdown-toggle4' data-toggle='dropdown'>Teams <span class='caret'></span></button><ul class='dropdown-menu' role='menu'>".$otherteams."</ul></div>";
							}
							?>
						</div><!-- /input-group -->
				  </div>
				</div>
			</form>
		</div>
		<div class="row">
		<!--left blank-->
		</div>
<script>
jQuery("#s2").focus();
</script>							
	
<?php
			if ($teamparent){
				$parentteam = get_term_by('id',$teamparent,'team');
				echo "<h3><i class='glyphicon glyphicon-chevron-left'></i> <a href='".site_url()."/team/".$parentteam->slug."'>".$parentteam->name."</a></h3>";
			}
?>	<div id="peoplenav"><a name='teamtop'></a>
			<div class='col-lg-12'><h2><?php echo $teamname; ?></h2>
			
	</div>
<?php
$terms = get_terms('team',array('hide_empty'=>false,'parent' => $termid));
		if ($terms) {
			$teamstr = '';
	  		foreach ((array)$terms as $taxonomy ) {
	  		    $themeid = $taxonomy->term_id;
	  		    $themeURL= $taxonomy->slug;
	  			$teamstr.= "<a href='#{$themeURL}'>".$taxonomy->name."</a> <span class='glyphicon glyphicon-stop light small'> </span> ";
			}
			$teamstr=substr($teamstr, 0, -60);
			echo "<div class=col-lg-12 col-md-12 col-sm-12'><strong>Sub-teams: </strong><br>".$teamstr."</div>";
		}  

	 
//***********************************************************************************************
			//query all sub teams for this team
	 		$term_query = get_terms('team',array('hide_empty'=>false,'parent' => $termid,));	 		
 			$allterms= array();
 			$iteams = array();
 			$iteams[] = $termid;
 			$multipleteams = false;
 			foreach ($term_query as $tq){
	 			$allterms[] = $tq->term_id;
	 			$iteams[] = $tq->term_id;
	 			$multipleteams = true;
 			}
 			$allterms[] = $termid; //add current team onto the the array
 			$allterms = implode(",", $allterms);//prepare for sql query

//custom sql query returns users in the current team sorted by grade
			$chevron=0;
 			foreach ($iteams as $tq){
	 		
	 		$gradehead='';

			$newteam = get_term_by('id', $tq, 'team');//print_r($newteam);

			if ($chevron!=0){
				echo "<a name='{$newteam->slug}'></a><div class='col-lg-12 col-md-12 col-sm-12  home page'><div class='category-block'><h3>".$newteam->name;
				echo " <a href='#teamtop'><span class='glyphicon glyphicon-chevron-up'></span></a>";	
				echo "</h3></div></div>";
			} 
			$chevron=1;
 			
	 		$q = "select user_id from wp_usermeta join wp_terms on wp_terms.term_id = wp_usermeta.meta_value where user_id in (select user_id from wp_usermeta as a where a.meta_key = 'user_team' and a.meta_value = ".$tq." ) and meta_key = 'user_grade' ;
 "; 
 			$user_query = $wpdb ->get_results($q);
 			$counter=0;
 			$tcounter=0;
 			$uid = array();
 			$ugrade = array();
 			$ulastname = array();
 			
 			
 			foreach ($user_query as $u){//print_r($u);
	 			$uid[] = $u->user_id;
	 			$ulastname[] = get_user_meta($u->user_id,'last_name',true);
	 			$g = get_user_meta($u->user_id,'user_grade',true); 
	 			$ugrade[] =  $g['slug'];		 			
 			}
 			
				
 			array_multisort($ugrade, $ulastname, $uid);
 			
 			foreach ($uid as $u){//print_r($u);
 				$g = get_user_meta($u,'user_grade',false);
 				$l = get_user_meta($u,'last_name',false);
 				$alreadyshown[$u]=true;
 				
 				$userid =  $u;

				if ($userid ==  $teamleaderid) continue; //don't output if this person is the team head and already displayed


				$newgrade = get_user_meta($userid,'user_grade',true);

				if ($newgrade['slug']!=$gradehead) {
					$gradehead=$newgrade['slug'];
					echo "<div class='col-lg-12 col-md-12 col-sm-12 '><div class='home page'><h3 class='light'>".$newgrade['name']."</h3></div></div>";
					$counter=0;
				}
				

				$context = get_user_meta($userid,'user_job_title',true);
				if ($context=='') $context="staff";
				$icon = "user";			
				$user_info = get_userdata($userid);
				$userurl = site_url().'/staff/'.$user_info->user_login;
				$displayname = get_user_meta($userid ,'first_name',true )." ".get_user_meta($userid ,'last_name',true );					
				if ( function_exists('get_wp_user_avatar')){
					$image_url = get_wp_user_avatar($userid,130,'left');
				} else {
					$image_url = get_avatar($userid,130);
				}
				$image_url = str_replace('avatar ', 'avatar img img-responsive' , $image_url);

				if ($directorystyle==1){
					$avatarhtml = str_replace('avatar-66', 'avatar-66 pull-left indexcard-avatar img img-circle', get_avatar($userid,66));
				}else{
					$avatarhtml = str_replace('avatar-66', 'avatar-66 pull-left indexcard-avatar img', get_avatar($userid,66));
				}

				if ($fulldetails){
						
						echo "<div class='col-lg-4 col-md-4 col-sm-6'><div class='media well well-sm'><a href='".site_url()."/staff/".$user_info->user_nicename."/'>".$avatarhtml."</a><div class='media-body'><p><a href='".site_url()."/staff/".$user_info->user_nicename."/'><strong>".$displayname."</strong></a><br>";

						// display team name(s)
						$poduser = new Pod ('user' , $userid);
						if ( get_user_meta($userid ,'user_job_title',true )) : 
			
							echo get_user_meta($userid ,'user_job_title',true )."<br>";
			
						endif;

						
						if ( get_user_meta($userid ,'user_telephone',true )) : 
			
							echo '<i class="glyphicon glyphicon-earphone"></i> <a href="tel:'.str_replace(" ", "", get_user_meta($userid ,"user_telephone",true )).'">'.get_user_meta($userid ,'user_telephone',true )."</a><br>";
			
						endif; 
			
						if ( get_user_meta($userid ,'user_mobile',true ) && $showmobile ) : 
			
							echo '<i class="glyphicon glyphicon-phone"></i> <a href="tel:'.str_replace(" ", "", get_user_meta($userid ,"user_mobile",true )).'">'.get_user_meta($userid ,'user_mobile',true )."</a><br>";
			
						 endif;
			
							echo  '<a href="mailto:'.$user_info->user_email.'">Email '. $user_info->first_name. '</a></p></div></div></div>';
							
							$counter++;	
							$tcounter++;	
					
				} //end full details
				else { 
					echo "<div class='col-lg-4 col-md-4 col-sm-6'><div class='indexcard'><a href='".site_url()."/staff/".$user_info->user_nicename."/'><div class='media'>".$avatarhtml."<div class='media-body'><strong>".$displayname."</strong><br>";
						// display team name(s)
						$poduser = new Pod ('user' , $userid);
						
							if ( get_user_meta($userid ,'user_job_title',true )) echo '<span class="small">'.get_user_meta($userid ,'user_job_title',true )."</span><br>";

							if ( get_user_meta($userid ,'user_telephone',true )) echo '<span class="small"><i class="glyphicon glyphicon-earphone"></i> '.get_user_meta($userid ,'user_telephone',true )."</span><br>";
							if ( get_user_meta($userid ,'user_mobile',true ) && $showmobile ) echo '<span class="small"><i class="glyphicon glyphicon-phone"></i> '.get_user_meta($userid ,'user_mobile',true )."</span>";
											
							echo "</div></div></div></div></a>";
							$counter++;	
				}	
 				 			
 			}
echo "<div class='col-lg-12 col-md-12 col-sm-12'>";
		//retrieve all staff for the team and sub teams including those without a grade
		//then display those not already shown as part of a grade		



//*** find ungraded staff

$q = "select distinct t1.user_id from wp_usermeta as t1
left outer join wp_terms on wp_terms.term_id = t1.meta_value 
WHERE t1.user_id in (select a.user_id from wp_usermeta as a where a.meta_key = 'user_team' and a.meta_value = ".$newteam->term_id." ) ";
		
		 $user_query = $wpdb ->get_results($q);

		 $oktoshow=false;
		 foreach ($user_query as $u){ // check for those already displayed
			 $uu = $u->user_id;
			 if (!$alreadyshown[$uu]){
			 	$oktoshow=true;
			 }
		 }
		 
		 if ($oktoshow){
					echo "<div class='col-lg-12 col-md-12 col-sm-12  home page'><div class='row'><h3 class='light'>Other</h3></div></div><div class='row'>";
		 }
		 foreach ($user_query as $u){ 
			 $uu = $u->user_id;
		 	if (!$alreadyshown[$uu]){
			 	
			 	//show remaining
 				
 				$userid =  $uu;

				if ($userid ==  $teamleaderid) continue; //don't output if this person is the team head and already displayed


				$context = get_user_meta($userid,'user_job_title',true);
				if ($context=='') $context="staff";
				$icon = "user";			
				$user_info = get_userdata($userid);
				$userurl = site_url().'/staff/'.$user_info->user_login;
				$displayname = get_user_meta($userid ,'first_name',true )." ".get_user_meta($userid ,'last_name',true );					
				if ( function_exists('get_wp_user_avatar')){
					$image_url = get_wp_user_avatar($userid,130,'left');
				} else {
					$image_url = get_avatar($userid,130);
				}
				$image_url = str_replace('avatar ', 'avatar img' , $image_url);

				if ($directorystyle==1){
					$avatarhtml = str_replace('avatar-66', 'avatar-66 pull-left indexcard-avatar img img-circle', get_avatar($userid,66));
				}else{
					$avatarhtml = str_replace('avatar-66', 'avatar-66 pull-left indexcard-avatar img', get_avatar($userid,66));
				}

				if ($fulldetails){
						
						echo "<div class='col-lg-4 col-md-4 col-sm-6'><div class='media well well-sm'><a href='".site_url()."/staff/".$user_info->user_nicename."/'>".$avatarhtml."</a><div class='media-body'><p><a href='".site_url()."/staff/".$user_info->user_nicename."/'><strong>".$displayname."</strong></a><br>";

						// display team name(s)
						$poduser = new Pod ('user' , $userid);

						if ( get_user_meta($userid ,'user_job_title',true )) : 
			
							echo get_user_meta($userid ,'user_job_title',true )."<br>";
			
						endif;

						
						if ( get_user_meta($userid ,'user_telephone',true )) : 
			
							echo '<i class="glyphicon glyphicon-earphone"></i> <a href="tel:'.str_replace(" ", "", get_user_meta($userid ,"user_telephone",true )).'">'.get_user_meta($userid ,'user_telephone',true )."</a><br>";
			
						endif; 
			
						if ( get_user_meta($userid ,'user_mobile',true ) && $showmobile ) : 
			
							echo '<i class="glyphicon glyphicon-phone"></i> <a href="tel:'.str_replace(" ", "", get_user_meta($userid ,"user_mobile",true )).'">'.get_user_meta($userid ,'user_mobile',true )."</a><br>";
			
						 endif;
			
							echo  '<a href="mailto:'.$user_info->user_email.'">Email '. $user_info->first_name. '</a></p></div></div></div>';
							
							$counter++;	
							$tcounter++;	
					
				} //end full details
				else { 
					echo "<div class='col-lg-4 col-md-4 col-sm-6'><div class='indexcard'><a href='".site_url()."/staff/".$user_info->user_nicename."/'><div class='media'>".$avatarhtml."<div class='media-body'><strong>".$displayname."</strong><br>";
						// display team name(s)
						$poduser = new Pod ('user' , $userid);
						
							if ( get_user_meta($userid ,'user_job_title',true )) echo '<span class="small">'.get_user_meta($userid ,'user_job_title',true )."</span><br>";

							if ( get_user_meta($userid ,'user_telephone',true )) echo '<span class="small"><i class="glyphicon glyphicon-earphone"></i> '.get_user_meta($userid ,'user_telephone',true )."</span><br>";
							if ( get_user_meta($userid ,'user_mobile',true ) && $showmobile ) echo '<span class="small"><i class="glyphicon glyphicon-phone"></i> '.get_user_meta($userid ,'user_mobile',true )."</span>";
											
							echo "</div></div></div></div></a>";
							$counter++;	
				}	
 				 		 	}//endif


		 }
if ($oktoshow){
	echo "</div>";
}


//***



echo "</div>"; 			
		}//end for 			
		?>
				</div>
			</div>
		</div>
	</div>

</div>
<?php get_footer(); ?>