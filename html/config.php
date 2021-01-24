<?php
try{
	if(isset($this)){
	    if(request_exists("iframe")) {
            $this->set_header_file('blank.tpl');
            $this->set_footer_file('blank.tpl');
        }else{
            $this->set_header_file('entete.tpl');
            $this->set_footer_file('piedpage.tpl');
        }
        $this->set_append_file('append.tpl');
	    $bodyclass=[];
	    $bodyclass[] = 'body_onlyphone';
	    $bodyclass[] = 'sidebar-mini';
	    $bodyclass[] = 'layout-fixed';
	    if(request_exists("iframe")){
            $bodyclass[] = 'iframe';
        }
        $this->set_body_class(implode(" ",$bodyclass));
        $this->set_prefix_title($_ENV['LOGO_NAME']);
		$this->add_entete('<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />');
		$this->add_entete('<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />');
		$this->add_entete('<meta property="og:site_name" content="'.htmlentities($_ENV['LOGO_NAME'] . ' - ' . $_ENV['SITE_SOUS_TITRE']).'" />');
		$this->add_entete('<meta property="og:locale" content="fr_FR" />');
		$this->add_entete('<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">');
		if(!isset($this->remove_search_header) || $this->remove_search_header==false){
            $this->add_entete('
    <!--[if gte IE 9]>
      <style type="text/css">
        .gradient {
           filter: none;
        }
      </style>
    <![endif]-->
    
    <script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "WebSite",
      "name": "'.$_ENV['LOGO_SLOGAN'].'",
      "alternateName": "'.$_ENV['LOGO_NAME'].'",
      "url": "https://'.$_ENV['SITE_URL'].'",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://'.$_ENV['SITE_URL'].'/search?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }  
    }
    </script>
    
    ');
        }
	}
}catch(Exception $ex){}