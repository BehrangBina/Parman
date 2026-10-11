<?php
/**
 * LOCAL ONLY. Rebuilds the local copy of the live membership form (Fluent Forms #3,
 * "فرم هموندی") in the Figma "Form-Hamvandi" layout (740:2198 / desktop 1181:5631).
 *
 * Field names are the live form's own (names, datetime, input_text, …): Fluent stores
 * entries by name, and the 178 live entries, the three emails and the weekly digest all
 * depend on them. Only order, labels, placeholders and styling follow Figma.
 * Removed (not in Figma): file-upload (education document), "how did you find us"
 * (input_radio_2 + description, description_2, description_3), net_promoter_score.
 * Added: whatsapp_consent (opt-in for the WhatsApp welcome message).
 *
 * Run inside the WordPress container (from the repo root):
 *   docker cp tools/build-membership-form.php local-wordpress-703-wordpress-1:/tmp/
 *   docker exec local-wordpress-703-wordpress-1 php /tmp/build-membership-form.php
 *
 * The ID upload (input_file) and the international phone field are Fluent Forms Pro;
 * without Pro (local) the upload is left out and the phone is a plain tel input.
 */

require '/var/www/html/wp-load.php';

if ( 'production' === wp_get_environment_type() ) {
	exit( "Refusing to run on production.\n" );
}

const IMP_FORM_ID    = 3;
const IMP_FORM_TITLE = 'فرم هموندی';

global $wpdb;
$forms_table = $wpdb->prefix . 'fluentform_forms';
$meta_table  = $wpdb->prefix . 'fluentform_form_meta';
$has_pro     = defined( 'FLUENTFORMPRO' );

/* ---------- field helpers ---------- */

$n    = 0;
$uid  = function () use ( &$n ) { return 'el_imp_member_' . ( ++$n ); };
$req  = function ( $on = true, $msg = 'این فیلد الزامی است' ) { return array( 'required' => array( 'value' => $on, 'message' => $msg ) ); };
$opt  = function ( $labels ) { return array_map( function ( $l ) { return array( 'label' => $l, 'value' => $l, 'calc_value' => '' ); }, $labels ); };
$when = function ( $field, $value ) {
	return array( 'type' => 'any', 'status' => true, 'conditions' => array( array( 'field' => $field, 'value' => $value, 'operator' => '=' ) ) );
};

$text = function ( $name, $label, $placeholder = '', $required = false, $type = 'text', $logic = array() ) use ( $uid, $req ) {
	return array(
		'element'        => 'input_text',
		'attributes'     => array( 'type' => $type, 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => $placeholder, 'maxlength' => '' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'help_message' => '', 'prefix_label' => '', 'suffix_label' => '', 'validation_rules' => $req( $required ), 'conditional_logics' => $logic ),
		'editor_options' => array( 'title' => 'Simple Text', 'icon_class' => 'ff-edit-text', 'template' => 'inputText' ),
		'uniqElKey'      => $uid(),
	);
};
$name_part = function ( $name, $label, $placeholder, $visible ) use ( $req ) {
	return array(
		'element'        => 'input_text',
		'attributes'     => array( 'type' => 'text', 'name' => $name, 'value' => '', 'id' => '', 'class' => '', 'placeholder' => $placeholder, 'maxlength' => '' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'help_message' => '', 'visible' => $visible, 'label_placement' => 'top', 'validation_rules' => $req( $visible ), 'conditional_logics' => array() ),
		'editor_options' => array( 'template' => 'inputText' ),
	);
};
$names = function () use ( $uid, $name_part ) {
	return array(
		'element'        => 'input_name',
		'attributes'     => array( 'name' => 'names', 'data-type' => 'name-element' ),
		'settings'       => array( 'container_class' => '', 'admin_field_label' => 'نام و نام خانوادگی', 'conditional_logics' => array(), 'label_placement' => 'top' ),
		'fields'         => array(
			'first_name'  => $name_part( 'first_name', 'نام', 'نام', true ),
			'middle_name' => $name_part( 'middle_name', 'نام مستعار', '', false ),
			'last_name'   => $name_part( 'last_name', 'نام خانوادگی', 'نام خانوادگی', true ),
		),
		'editor_options' => array( 'title' => 'Name Fields', 'element' => 'name-fields', 'icon_class' => 'ff-edit-name', 'template' => 'nameFields' ),
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
$radio = function ( $name, $label, $choices, $required = true, $layout = '' ) use ( $uid, $req, $opt ) {
	return array(
		'element'        => 'input_radio',
		'attributes'     => array( 'type' => 'radio', 'name' => $name, 'value' => '' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'display_type' => '', 'help_message' => '', 'randomize_options' => 'no', 'advanced_options' => $opt( $choices ), 'calc_value_status' => false, 'enable_image_input' => false, 'layout_class' => $layout, 'validation_rules' => $req( $required ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Radio Field', 'icon_class' => 'ff-edit-radio', 'element' => 'input-radio', 'template' => 'inputCheckable' ),
		'uniqElKey'      => $uid(),
	);
};
$textarea = function ( $name, $label, $placeholder, $required = false ) use ( $uid, $req ) {
	return array(
		'element'        => 'textarea',
		'attributes'     => array( 'name' => $name, 'value' => '', 'class' => '', 'placeholder' => $placeholder, 'rows' => 4, 'cols' => 2, 'maxlength' => '' ),
		'settings'       => array( 'container_class' => '', 'label' => $label, 'label_placement' => '', 'admin_field_label' => $label, 'help_message' => '', 'validation_rules' => $req( $required ), 'conditional_logics' => array() ),
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
$checkbox_tnc = function ( $name, $html_text, $required = true ) use ( $uid, $req ) {
	return array(
		'element'        => 'terms_and_condition',
		'attributes'     => array( 'type' => 'checkbox', 'name' => $name, 'value' => false, 'class' => '' ),
		'settings'       => array( 'tnc_html' => $html_text, 'has_checkbox' => true, 'admin_field_label' => wp_strip_all_tags( $html_text ), 'container_class' => '', 'validation_rules' => $req( $required, 'لطفاً این مورد را تأیید کنید' ), 'conditional_logics' => array() ),
		'editor_options' => array( 'title' => 'Terms & Conditions', 'icon_class' => 'ff-edit-terms-condition', 'template' => 'termsCheckbox' ),
		'uniqElKey'      => $uid(),
	);
};
$file = function ( $name, $label ) use ( $uid ) {
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

/* ---------- the form, in Figma order, with the live field names ---------- */

$docs_html = '<div class="imp-ff-docs">'
	. '<p class="imp-ff-docs__title">لطفاً پیش از ارسال فرم، اسناد زیر را با دقت مطالعه فرمایید</p>'
	. '<p class="imp-ff-docs__links">'
	. '<a class="imp-ff-pill" href="/party-constitution/" target="_blank">اساسنامه</a>'
	. '<a class="imp-ff-pill" href="/affidavit/" target="_blank">سوگندنامه</a>'
	. '<a class="imp-ff-pill" href="/partys-motto/" target="_blank">مرامنامه حزب</a>'
	. '</p>'
	. '<p class="imp-ff-docs__fee"><a href="/donate/#fee" data-imp-dialog="imp-fee-dialog">درباره هزینه هموندی</a></p>'
	. '</div>';

$fields = array(
	$names(),
	$date( 'datetime', 'زادروز' ),
	$email( 'email', 'آدرس ایمیل', 'you@example.com' ),
	$text( 'phone', 'شماره تلفن', '+49 000 0000000', true, 'tel' ), // Pro: replaced by the international phone field below
	$text( 'input_text_2', 'شغل', 'شغل خود را وارد کنید', true ),
	$text( 'input_text_3', 'تحصیلات', 'آخرین مدرک تحصیلی', true ),
	$text( 'input_text_7', 'لطفاً آیدی تلگرام خود را وارد نمایید', '@telegram_id', true ),
	$text( 'input_text', 'زادگاه', 'شهر یا کشور محل تولد', true ),
	$radio( 'dropdown', 'جنسیت', array( 'زن', 'مرد', 'دگرباش' ), true, 'ff_list_inline' ),
	$two_columns( $text( 'input_text_1', 'شهر محل اقامت', 'شهر', true ), $country( 'country-list', 'کشور محل اقامت' ) ),
	$text( 'subject', 'سوابق کارهای سیاسی قبلی', 'در صورت وجود' ),
	$textarea( 'message', 'انگیزه درخواست هموندی', 'لطفاً کوتاه شرح دهید', true ),
	$radio( 'input_radio', 'آیا اکنون هموند سازمان سیاسی دیگری هستید؟', array( 'بله', 'خیر' ) ),
	$text( 'input_text_5', 'کدام حزب یا سازمان؟', '', true, 'text', $when( 'input_radio', 'بله' ) ),
	$radio( 'input_radio_1', 'آیا شما به غیر از ایران دارای ملیت دیگری میباشید؟', array( 'بله', 'خیر' ) ),
	$text( 'input_text_6', 'ملیت چه کشوری؟', '', false, 'text', $when( 'input_radio_1', 'بله' ) ),
	'__FILE__',
	$html( $docs_html ),
	$radio( 'membership_fee_commitment', 'حق هموندی ماهانه ۹٫۹۹ دلار آمریکا و سالانه ۹۹ دلار است. دانشجویان و پناهجویان از پرداخت حق هموندی معاف هستند. آیا امکان تضمین پرداخت حق هموندی را دارید؟', array( 'بله', 'خیر', 'معاف هستم (دانشجو یا پناهجو)' ), false ),
	$checkbox_tnc( 'confirm_documents', 'اینجانب تأیید می‌کنم که مرامنامه، راهنمای هموندی و سوگندنامه را مطالعه کرده‌ام و با مفاد آن موافقم.' ),
	$checkbox_tnc( 'terms-n-condition', 'با ارسال این فرم، موافقت خود را با ذخیره و پردازش اطلاعات شخصی‌ام مطابق مقررات حفاظت از داده‌ها (GDPR) و قوانین آلمان اعلام می‌کنم. <a href="https://gdpr.eu" target="_blank" rel="noopener">جزئیات بیشتر</a>' ),
	$checkbox_tnc( 'whatsapp_consent', 'مایلم پیام خوش‌آمدگویی و اطلاع‌رسانی‌های هموندی را از طریق واتساپ (به شماره بالا) دریافت کنم.', false ),
);

$out = array();
foreach ( $fields as $f ) {
	if ( '__FILE__' === $f ) {
		if ( $has_pro ) {
			$out[] = $file( 'file-upload_1', 'آپلود مدرک شناسایی' );
		}
		continue;
	}
	$out[] = $f;
}
foreach ( $out as $i => $f ) {
	$out[ $i ]['index'] = $i;
}
$form_fields = array(
	'fields'       => $out,
	'submitButton' => array(
		'uniqElKey'      => 'el_imp_member_submit',
		'element'        => 'button',
		'attributes'     => array( 'type' => 'submit', 'class' => '' ),
		'settings'       => array( 'align' => 'center', 'button_style' => 'default', 'container_class' => '', 'help_message' => '', 'background_color' => '#D5AF30', 'button_size' => 'md', 'color' => '#243F88', 'button_ui' => array( 'type' => 'default', 'text' => 'ارسال', 'img_url' => '' ) ),
		'editor_options' => array( 'title' => 'Submit Button' ),
	),
);

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

/* ---------- the live form's three emails (same recipients, subjects, conditions, text) ---------- */

$notification = function ( $name, $send_to, $subject, $message, $conditions = array(), $reply_to = '' ) {
	return array(
		'name'           => $name,
		'sendTo'         => $send_to,
		'fromName'       => '',
		'fromEmail'      => '',
		'replyTo'        => $reply_to,
		'bcc'            => '',
		'cc'             => '',
		'subject'        => $subject,
		'message'        => $message,
		'conditionals'   => array( 'status' => (bool) $conditions, 'type' => 'all', 'conditions' => $conditions ? $conditions : array( array( 'field' => null, 'operator' => '=', 'value' => null ) ) ),
		'enabled'        => true,
		'email_template' => '',
	);
};
$to_applicant = array( 'type' => 'field', 'email' => null, 'field' => 'email', 'routing' => array() );
$cell         = 'border: 1px solid #ddd; padding: 6px 10px;';
$office_rows  = array(
	'نام و نام خانوادگی'     => '{inputs.names.first_name} {inputs.names.last_name}',
	'تعهد پرداخت حق هموندی'  => '{inputs.membership_fee_commitment}',
	'ایمیل'                  => '{inputs.email}',
	'شماره تلفن'             => '{inputs.phone}',
	'آیدی تلگرام'            => '{inputs.input_text_7}',
	'کشور محل اقامت'         => '{inputs.country-list}',
	'شهر محل اقامت'          => '{inputs.input_text_1}',
	'شغل'                    => '{inputs.input_text_2}',
	'انگیزه درخواست هموندی'  => '{inputs.message}',
);
$office_html = '<div dir="rtl" style="font-family: Tahoma,Arial,sans-serif; text-align: right;"><p><strong>درخواست هموندی جدید دریافت شد.</strong></p><table style="border-collapse: collapse; font-size: 14px;"><tbody>';
foreach ( $office_rows as $label => $code ) {
	$office_html .= '<tr><td style="' . $cell . '"><strong>' . $label . '</strong></td><td style="' . $cell . '">' . $code . '</td></tr>';
}
$office_html .= '</tbody></table><p><a href="' . admin_url( 'admin.php?page=fluent_forms&route=entries&form_id=' . IMP_FORM_ID ) . '#/entries/{submission.id}">مشاهده پرونده کامل در سامانه</a></p><p style="color: #888; font-size: 12px;">به جهت بررسی</p></div>';

$welcome = function ( $with_fee ) {
	return '<div>درود بر شما {inputs.names.first_name} {inputs.names.last_name}</div><p>&nbsp;</p>'
		. '<div>عضویت شما با موفقیت ثبت شد ✅</div><div>'
		. '<p>از کارگروه هموندی با شما تماس برقرار خواهد شد و شما به گروه واتساپی پارمان هدایت می‌شوید و سپس به گروه تلگرام، و با توجه به تخصصی که اعلام فرموده‌اید در کارگروه، کمیسیون مربوطه شروع به فعالیت خواهید کرد.</p>'
		. ( $with_fee ? '<p>برای پرداخت حق هموندی، از طریق پیوند زیر اقدام فرمایید:<br /><a href="https://donorbox.org/membership-930550">https://donorbox.org/membership-930550</a></p>' : '' )
		. '<p>با سپاس<br />دبیرخانه حزب پادشاهی ایرانیان<br />میترا سالار</p><br />E-Mail: Info@Iranianmonarchy.info</div>';
};

$notifications = array(
	$notification( 'New Notification', array( 'type' => 'email', 'email' => 'office@iranianmonarchy.info', 'field' => null, 'routing' => array() ), 'هموند جدید ارشد', $office_html ),
	$notification( 'ایمیل خوش‌آمدگویی — بدون پرداخت حق هموندی', $to_applicant, 'عضویت شما با موفقیت انجام شد ✅', $welcome( false ), array( array( 'field' => 'membership_fee_commitment', 'operator' => '!=', 'value' => 'بله' ) ), '{inputs.email}' ),
	$notification( 'ایمیل خوش‌آمدگویی — با پرداخت حق هموندی', $to_applicant, 'عضویت شما با موفقیت انجام شد ✅', $welcome( true ), array( array( 'field' => 'membership_fee_commitment', 'operator' => '=', 'value' => 'بله' ) ), '{inputs.email}' ),
);

/* ---------- save as local form #3 ---------- */

$now  = current_time( 'mysql' );
$row  = array( 'title' => IMP_FORM_TITLE, 'status' => 'published', 'form_fields' => wp_json_encode( $form_fields, JSON_UNESCAPED_UNICODE ), 'has_payment' => 0, 'type' => 'form', 'updated_at' => $now );
if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $forms_table WHERE id = %d", IMP_FORM_ID ) ) ) {
	$wpdb->update( $forms_table, $row, array( 'id' => IMP_FORM_ID ) );
} else {
	$wpdb->insert( $forms_table, $row + array( 'id' => IMP_FORM_ID, 'created_by' => 1, 'created_at' => $now ) );
}
$wpdb->delete( $meta_table, array( 'form_id' => IMP_FORM_ID ) );
$wpdb->insert( $meta_table, array( 'form_id' => IMP_FORM_ID, 'meta_key' => 'formSettings', 'value' => wp_json_encode( $form_settings, JSON_UNESCAPED_UNICODE ) ) );
$wpdb->insert( $meta_table, array( 'form_id' => IMP_FORM_ID, 'meta_key' => 'template_name', 'value' => 'blank_form' ) );
foreach ( $notifications as $item ) {
	$wpdb->insert( $meta_table, array( 'form_id' => IMP_FORM_ID, 'meta_key' => 'notifications', 'value' => wp_json_encode( $item, JSON_UNESCAPED_UNICODE ) ) );
}

echo 'form_id=' . IMP_FORM_ID . ' fields=' . count( $out ) . ' pro=' . ( $has_pro ? 'yes' : 'no' ) . "\n";
