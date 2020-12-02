<?php
error_reporting(E_ALL);
require('../SSI.php');
$context['page_title_html_safe'] = 'choose plane';

template_header();
$context['sub_template'] = 'registration_form';
loadTemplate('Register');
template_footer();
?>
