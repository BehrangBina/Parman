<?php
/**
 * Builds the Figma "Form-Hamvandi" (740:2198) membership form as a Fluent Forms form,
 * and writes docs/membership-form.fluentform.json for importing on the live site
 * (Fluent Forms → Tools → Import Forms).
 *
 * Run inside the WordPress container (from the repo root):
 *   docker cp tools/build-membership-form.php local-wordpress-703-wordpress-1:/tmp/
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/build-membership-form.php /tmp/membership-form.fluentform.json
 *   docker cp local-wordpress-703-wordpress-1:/tmp/membership-form.fluentform.json docs/
 *
 * The ID-upload field (input_file) is Fluent Forms Pro only: it is always written to the
 * export, but only saved into the local form when Pro is active.
 */

require '/var/www/html/wp-load.php';

global $wpdb;
$forms_table = $wpdb->prefix . 'fluentform_forms';
$meta_table  = $wpdb->prefix . 'fluentform_form_meta';
$has_pro     = defined( 'FLUENTFORMPRO' );
$export_path = isset( $argv[1] ) ? $argv[1] : '/tmp/membership-form.fluentform.json';

/* ---------- field helpers ---------- */

$n   = 0;
$uid = function () use ( &$n ) { return 'el_imp_member_' . ( ++$n ); };
$req = function ( $on = true, $msg = 'این فیلد الزامی است' ) { return array( 'required' => array( 'value' => $on, 'message' => $msg ) ); };
$opt = function ( $labels ) { return array_map( function ( $l ) { return array( 'label' => $l, 'value' => $l, 'calc_value' => '' ); }, $labels ); };

$text = function ( $name, $label, $placeholder = '', $required = false, $type = 'text' ) use ( $uid, $req ) {
	return array(
		'element'        => 'input_text',
		'attributes'     => array( 'type' => $type, 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => $placeholder, 'maxlength' => '' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'help_message' => '', 'prefix_label' => '', 'suffix_label' => '', 'validation_rules' => $req( $required ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Simple Text', 'icon_class' => 'ff-edit-text', 'template' => 'inputText' ),
		'uniqElKey'      => $uid(),
	);
};
$email = function ( $name, $label, $placeholder ) use ( $uid, $req ) {
	return array(
		'element'        => 'input_email',
		'attributes'     => array( 'type' => 'email', 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => $placeholder ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'help_message' => '', 'validation_rules' => array_merge( $req( true ), array( 'email' => array( 'value' => true, 'message' => 'لطفاً یک ایمیل معتبر وارد کنید' ) ) ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Email Address', 'icon_class' => 'ff-edit-email', 'template' => 'inputText' ),
		'uniqElKey'      => $uid(),
	);
};
$date = function ( $name, $label ) use ( $uid, $req ) {
	return array(
		'element'        => 'input_date',
		'attributes'     => array( 'type' => 'text', 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => 'انتخاب' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'date_format' => 'd/m/Y', 'date_config' => '', 'is_time_enabled' => false, 'help_message' => '', 'validation_rules' => $req( true ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Time & Date', 'icon_class' => 'ff-edit-date', 'template' => 'inputText' ),
		'uniqElKey'      => $uid(),
	);
};
$country = function ( $name, $label ) use ( $uid, $req ) {
	return array(
		'element'        => 'select_country',
		'attributes'     => array( 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => 'انتخاب' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'help_message' => '', 'enable_select_2' => 'no', 'country_list' => array( 'active_list' => 'all', 'visible_list' => array(), 'hidden_list' => array() ), 'validation_rules' => $req( true ), 'conditional_logics' => array() ),
		'options'        => array( 'US' => 'US' ),
		'editor_options' => array( 'title' => 'Country List', 'element' => 'country-list', 'icon_class' => 'ff-edit-country', 'template' => 'selectCountry' ),
		'uniqElKey'      => $uid(),
	);
};
$radio = function ( $name, $label, $choices, $layout = 'ff_list_inline' ) use ( $uid, $req, $opt ) {
	return array(
		'element'        => 'input_radio',
		'attributes'     => array( 'type' => 'radio', 'name' => $name, 'value' => '' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'display_type' => '', 'help_message' => '', 'randomize_options' => 'no', 'advanced_options' => $opt( $choices ), 'calc_value_status' => false, 'enable_image_input' => false, 'layout_class' => $layout, 'validation_rules' => $req( true ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Radio Field', 'icon_class' => 'ff-edit-radio', 'element' => 'input-radio', 'template' => 'inputCheckable' ),
		'uniqElKey'      => $uid(),
	);
};
$textarea = function ( $name, $label, $placeholder ) use ( $uid, $req ) {
	return array(
		'element'        => 'textarea',
		'attributes'     => array( 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => $placeholder, 'rows' => 4, 'cols' => 2, 'maxlength' => '' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'help_message' => '', 'validation_rules' => $req( false ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Text Area', 'icon_class' => 'ff-edit-textarea', 'template' => 'inputTextarea' ),
		'uniqElKey'      => $uid(),
	);
};
$html = function ( $markup ) use ( $uid ) {
	return array(
		'element'        => 'custom_html',
		'attributes'     => array(),
		'settings'       => array( 'html_codes' => $markup, 'conditional_logics' => array(), 'container_class' => '' ),
		'editor_options' => array( 'title' => 'Custom HTML', 'icon_class' => 'ff-edit-html', 'template' => 'customHTML' ),
		'uniqElKey'      => $uid(),
	);
};
$two_columns = function ( $right, $left ) use ( $uid ) {
	return array(
		'element'        => 'container',
		'attributes'     => array(),
		'settings'       => array( 'container_class' => '', 'conditional_logics' => array(), 'container_width' => '', 'is_width_auto_calc' => true ),
		'columns'        => array( array( 'width' => 50, 'fields' => array( $right ) ), array( 'width' => 50, 'fields' => array( $left ) ) ),
		'editor_options' => array( 'title' => 'Two Column Container', 'icon_class' => 'ff-edit-column-2' ),
		'uniqElKey'      => $uid(),
	);
};
$checkbox_tnc = function ( $name, $html_text ) use ( $uid, $req ) {
	return array(
		'element'        => 'terms_and_condition',
		'attributes'     => array( 'type' => 'checkbox', 'name' => $name, 'value' => false, 'class' => '' ),
		'settings'       => array( 'tnc_html' => $html_text, 'has_checkbox' => true, 'admin_field_label' => wp_strip_all_tags( $html_text ), 'container_class' => '', 'validation_rules' => $req( true, 'لطفاً این مورد را تأیید کنید' ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Terms & Conditions', 'icon_class' => 'ff-edit-terms-condition', 'template' => 'termsCheckbox' ),
		'uniqElKey'      => $uid(),
	);
};
$file = function ( $name, $label ) use ( $uid, $req ) {
	return array(
		'element'        => 'input_file',
		'attributes'     => array( 'type' => 'file', 'name' => $name, 'value' => '', 'class' => '' ),
		'settings'       => array(
			'container_class'    => '',
			'label'              => $label,
			'label_placement'    => '',
			'admin_field_label'  => $label,
			'btn_text'           => 'انتخاب فایل',
			'help_message'       => 'تصویر یا PDF کارت شناسایی (حداکثر ۵ مگابایت)',
			'file_location_type' => 'follow_global_settings',
			'validation_rules'   => array(
				'required'           => array( 'value' => true, 'message' => 'لطفاً تصویر مدرک شناسایی را بارگذاری کنید' ),
				'max_file_size'      => array( 'value' => 5242880, '_valueFrom' => 'MB', 'message' => 'حداکثر حجم فایل ۵ مگابایت است' ),
				'max_file_count'     => array( 'value' => 1, 'message' => 'فقط یک فایل مجاز است' ),
				'allowed_file_types' => array( 'value' => array( 'jpg|jpeg|gif|png|bmp', 'pdf' ), 'message' => 'فقط تصویر یا PDF مجاز است' ),
			),
			'conditional_logics' => array(),
		),
		'editor_options' => array( 'title' => 'File Upload', 'icon_class' => 'ff-edit-files', 'template' => 'inputFile' ),
		'uniqElKey'      => $uid(),
	);
};

/* ---------- the form, in Figma order ---------- */

$docs_html = '<div class="imp-ff-docs">'
	. '<p class="imp-ff-docs__title">لطفاً پیش از ارسال فرم، اسناد زیر را با دقت مطالعه فرمایید</p>'
	. '<p class="imp-ff-docs__links">'
	. '<a class="imp-ff-pill" href="/party-constitution/" target="_blank">اساسنامه</a>'
	. '<a class="imp-ff-pill" href="/affidavit/" target="_blank">سوگندنامه</a>'
	. '<a class="imp-ff-pill" href="/partys-motto/" target="_blank">مرامنامه حزب</a>'
	. '</p>'
	. '<p class="imp-ff-docs__fee"><a href="/donate/#fee" data-imp-dialog="imp-m-fee-dialog">درباره هزینه هموندی</a></p>'
	. '</div>';

$fields = array(
	$text( 'full_name', 'نام و نام خانوادگی', 'نام کامل خود را وارد کنید', true ),
	$date( 'birth_date', 'زادروز' ),
	$email( 'email', 'آدرس ایمیل', 'you@example.com' ),
	$text( 'phone', 'شماره تلفن', '+49 000 0000000', true, 'tel' ),
	$text( 'occupation', 'شغل', 'شغل خود را وارد کنید' ),
	$text( 'education', 'تحصیلات', 'آخرین مدرک تحصیلی' ),
	$text( 'telegram', 'لطفاً آیدی تلگرام خود را وارد نمایید', '@telegram_id' ),
	$country( 'birthplace', 'زادگاه' ),
	$radio( 'gender', 'جنسیت', array( 'زن', 'مرد', 'سایر' ) ),
	$two_columns( $text( 'city', 'شهر محل اقامت', 'شهر', true ), $country( 'country', 'کشور محل اقامت' ) ),
	$text( 'political_history', 'سوابق کارهای سیاسی قبلی', 'در صورت وجود' ),
	$textarea( 'motivation', 'انگیزه درخواست هموندی', 'لطفاً کوتاه شرح دهید' ),
	$radio( 'other_party', 'آیا اکنون هموند سازمان سیاسی دیگری هستید؟', array( 'بله', 'خیر' ), '' ),
	$radio( 'other_nationality', 'آیا شما به غیر از ایران دارای ملیت دیگری میباشید؟', array( 'بله', 'خیر' ), '' ),
	'__FILE__',
	$html( $docs_html ),
	$checkbox_tnc( 'confirm_documents', 'اینجانب تأیید می‌کنم که مرامنامه، راهنمای هموندی و سوگندنامه را مطالعه کرده‌ام و با آن موافقم.' ),
	$checkbox_tnc( 'privacy_consent', 'با ارسال این فرم، موافقت خود را با ذخیره و پردازش اطلاعات شخصی‌ام مطابق مقررات حفاظت از داده‌ها (GDPR) و قوانین آلمان اعلام می‌کنم. <a href="/privacy-policy/" target="_blank">جزئیات بیشتر</a>' ),
);

$build = function ( $with_file ) use ( $fields, $file ) {
	$out = array();
	foreach ( $fields as $f ) {
		if ( '__FILE__' === $f ) {
			if ( $with_file ) {
				$out[] = $file( 'id_document', 'تصویر مدرک شناسایی' );
			}
			continue;
		}
		$out[] = $f;
	}
	foreach ( $out as $i => $f ) {
		$out[ $i ]['index'] = $i;
	}
	return array(
		'fields'       => $out,
		'submitButton' => array(
			'uniqElKey'      => 'el_imp_member_submit',
			'element'        => 'button',
			'attributes'     => array( 'type' => 'submit', 'class' => '' ),
			'settings'       => array( 'align' => 'center', 'button_style' => 'default', 'container_class' => '', 'help_message' => '', 'background_color' => '#D5AF30', 'button_size' => 'md', 'color' => '#243F88', 'button_ui' => array( 'type' => 'default', 'text' => 'ارسال', 'img_url' => '' ) ),
			'editor_options' => array( 'title' => 'Submit Button' ),
		),
	);
};

$form_settings = array(
	'confirmation' => array(
		'redirectTo'           => 'samePage',
		'messageToShow'        => '<p>درخواست هموندی شما با موفقیت ارسال شد. پس از بررسی، با شما تماس خواهیم گرفت. سپاس!</p>',
		'customPage'           => null,
		'samePageFormBehavior' => 'hide_form',
		'customUrl'            => null,
	),
	'restrictions' => array( 'limitNumberOfEntries' => array( 'enabled' => false ), 'scheduleForm' => array( 'enabled' => false ), 'requireLogin' => array( 'enabled' => false ), 'denyEmptySubmission' => array( 'enabled' => true, 'message' => 'لطفاً فرم را تکمیل کنید.' ) ),
	'layout'       => array( 'labelPlacement' => 'top', 'helpMessagePlacement' => 'with_label', 'errorMessagePlacement' => 'inline', 'asteriskPlacement' => 'asterisk-left' ),
);
$notification = array(
	'name'         => 'اعلان درخواست هموندی جدید',
	'sendTo'       => array( 'type' => 'email', 'email' => '{wp.admin_email}', 'field' => '', 'routing' => array() ),
	'fromName'     => '', 'fromEmail' => '',
	'replyTo'      => '{inputs.email}',
	'bcc'          => '', 'cc' => '',
	'subject'      => 'درخواست هموندی جدید: {inputs.full_name}',
	'message'      => '<p>{all_data}</p>',
	'conditionals' => array( 'status' => false, 'type' => 'all', 'conditions' => array() ),
	'enabled'      => true,
	'email_template' => '',
);

/* ---------- save locally ---------- */

$title  = 'فرم هموندی (Figma 2026)';
$fields_local = wp_json_encode( $build( $has_pro ), JSON_UNESCAPED_UNICODE );
$now    = current_time( 'mysql' );
$id     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $forms_table WHERE title=%s", $title ) );
if ( $id ) {
	$wpdb->update( $forms_table, array( 'form_fields' => $fields_local, 'updated_at' => $now ), array( 'id' => $id ) );
} else {
	$wpdb->insert( $forms_table, array( 'title' => $title, 'status' => 'published', 'form_fields' => $fields_local, 'has_payment' => 0, 'type' => 'form', 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now ) );
	$id = (int) $wpdb->insert_id;
}
$wpdb->delete( $meta_table, array( 'form_id' => $id ) );
$metas = array(
	array( 'meta_key' => 'formSettings', 'value' => wp_json_encode( $form_settings, JSON_UNESCAPED_UNICODE ) ),
	array( 'meta_key' => 'notifications', 'value' => wp_json_encode( $notification, JSON_UNESCAPED_UNICODE ) ),
	array( 'meta_key' => 'template_name', 'value' => 'blank_form' ),
);
foreach ( $metas as $meta ) {
	$wpdb->insert( $meta_table, array( 'form_id' => $id, 'meta_key' => $meta['meta_key'], 'value' => $meta['value'] ) );
}

/* ---------- export (always includes the ID upload) ---------- */

$export = array(
	array(
		'title'       => $title,
		'status'      => 'published',
		'form_fields' => $build( true ),
		'has_payment' => 0,
		'type'        => 'form',
		'conditions'  => null,
		'metas'       => $metas,
	),
);
file_put_contents( $export_path, wp_json_encode( $export, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );

echo "form_id=$id pro=" . ( $has_pro ? 'yes' : 'no' ) . " export=$export_path\n";
