<?php

class SuppleFieldRichText extends SuppleField {

	public $type_name = 'RichText';

	public function preProcess($value) {

		$r = parent::preProcess($value);
		//DEBUG:
		return $r;
		
		if ($this->allow_free_html) return $r;

		$allowed_tags = array('p', 'br');
		$allowed_attributes = array();
		$allowed_styles = array();
		$covert_to_p = array('h1', 'h2', 'h3', 'h4', 'h5', 'div');

		if ($this->show_toolbar_basicformat){
			$allowed_tags[] = 'strong';
			$allowed_tags[] = 'em';
			$allowed_tags[] = 'span';
			$allowed_styles[] = 'text-decoration';
		}

		if ($this->show_toolbar_styleformat){
			$allowed_tags[] = 'span';
			$allowed_styles[] = 'font-family';
			$allowed_styles[] = 'font-size';
		}

		if ($this->show_toolbar_align){
			$allowed_styles[] = 'text-align';
		}

		if ($this->show_toolbar_lists){
			$allowed_tags[] = 'ul';
			$allowed_tags[] = 'ol';
			$allowed_tags[] = 'li';
			$allowed_styles[] = 'padding-left';
		}

		if ($this->show_toolbar_link){
			$allowed_tags[] = 'a';
			$allowed_attributes[] = 'href';
			$allowed_attributes[] = 'target';
			$allowed_attributes[] = 'title';
		}

		require_once('include/simplehtmldom/simple_html_dom.php');
		$html = str_get_html($value);

		$this->cleanHtml($html, $allowed_tags, $allowed_attributes, $allowed_styles, $covert_to_p);

		$r = $html."";
		
		return $r;

	}

	public function postProcess($value) {
		$r = parent::postProcess($value);
		// return utf8_encode($value); //???
		return $r;
	}

	public function cleanHtml(&$html, $allowed_tags, $allowed_attributes, $allowed_styles, $covert_to_p){

		if (!is_object($html)) return;

		foreach ($html->find('*') as $i => $e){
			// CLEAN TAGS
			if (!in_array(strtolower($e->tag), $allowed_tags)){
				if (in_array(strtolower($e->tag), $covert_to_p)) {
					$e->tag = 'p';
					$this->cleanHtml($e, $allowed_tags, $allowed_attributes, $allowed_styles, $covert_to_p);
				} else {
					$e->outertext = $e->plaintext;
				}
			} else {
				$this->cleanHtml($e, $allowed_tags, $allowed_attributes, $allowed_styles, $covert_to_p);
			}
		}

		if (is_array($html->attr)){
			foreach ($html->attr as $a => $val){
				if ($a == 'style'){
					// CLEAN STYLE ATTRIBUTES
					$r = '';
					$styles = explode(';', $val);
					foreach ($styles as $s){
						$ps = explode(':', $s);
						if (isset($ps[0]) && isset($ps[1]) && in_array(strtolower($ps[0]), $allowed_styles)){
							$r .= $s . ";";
						}	
					}
					if ($r){
						$html->attr[$a] = $r;
					} else {
						unset($html->attr[$a]);
					}
				} else if (!in_array(strtolower($a), $allowed_attributes)) {
					// CLEAN ATTRIBUTES
					unset($html->attr[$a]);
				}
			}
		}
	}

}
