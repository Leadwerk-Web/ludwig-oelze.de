<?php
/**
 * Standalone Schadenfall form handling for Ludwig pages.
 *
 * The fallback form works without WPForms. Uploaded PDFs are validated, attached
 * to the owner email, and removed from temporary server storage after sending.
 *
 * @package Leadwerk_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'LEADWERK_SCHADENFALL_ACTION' ) ) {
	define( 'LEADWERK_SCHADENFALL_ACTION', 'leadwerk_schadenfall_submit' );
}

/**
 * Site owner recipient for Schadenfall notifications.
 *
 * @return string
 */
function leadwerk_theme_schadenfall_owner_email() {
	$email = '';
	if ( function_exists( 'leadwerk_theme_get_option_value' ) ) {
		$email = leadwerk_theme_get_option_value( 'schadenfall_owner_email', '' );
		if ( '' === trim( (string) $email ) ) {
			$email = leadwerk_theme_get_option_value( 'company_email', '' );
		}
	}

	$email = sanitize_email( (string) $email );
	if ( ! is_email( $email ) ) {
		$email = 'finanzen@ludwigoelze.com';
	}

	return (string) apply_filters( 'leadwerk_schadenfall_owner_email', $email );
}

/**
 * Sender email for Schadenfall notifications.
 *
 * @return string
 */
function leadwerk_theme_schadenfall_sender_email() {
	$email = '';
	if ( function_exists( 'leadwerk_theme_get_option_value' ) ) {
		$email = leadwerk_theme_get_option_value( 'schadenfall_sender_email', '' );
		if ( '' === trim( (string) $email ) ) {
			$email = leadwerk_theme_get_option_value( 'company_email', '' );
		}
	}

	$email = sanitize_email( (string) $email );
	if ( ! is_email( $email ) ) {
		$email = 'finanzen@ludwigoelze.com';
	}

	return (string) apply_filters( 'leadwerk_schadenfall_sender_email', $email );
}

/**
 * Sender name for Schadenfall notifications.
 *
 * @return string
 */
function leadwerk_theme_schadenfall_sender_name() {
	$name = '';
	if ( function_exists( 'leadwerk_theme_get_option_value' ) ) {
		$name = leadwerk_theme_get_option_value( 'schadenfall_sender_name', '' );
	}

	$name = sanitize_text_field( (string) $name );
	if ( '' === trim( $name ) || 'demo' === strtolower( trim( $name ) ) ) {
		$name = 'Ludwig Oelze';
	}

	return (string) apply_filters( 'leadwerk_schadenfall_sender_name', $name );
}

/**
 * wp_mail_from callback for this form only.
 *
 * @param string $from Existing sender.
 * @return string
 */
function leadwerk_theme_schadenfall_mail_from( $from ) {
	return leadwerk_theme_schadenfall_sender_email();
}

/**
 * wp_mail_from_name callback for this form only.
 *
 * @param string $from_name Existing sender name.
 * @return string
 */
function leadwerk_theme_schadenfall_mail_from_name( $from_name ) {
	return leadwerk_theme_schadenfall_sender_name();
}

/**
 * Force PHPMailer envelope sender for this form only.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer instance.
 * @return void
 */
function leadwerk_theme_schadenfall_configure_phpmailer( $phpmailer ) {
	$email = leadwerk_theme_schadenfall_sender_email();
	$name  = leadwerk_theme_schadenfall_sender_name();

	if ( is_email( $email ) ) {
		$phpmailer->Sender   = $email;
		$phpmailer->From     = $email;
		$phpmailer->FromName = $name;
	}
}

/**
 * Send Schadenfall mail with explicit From and envelope sender.
 *
 * @param string|array $to Recipient.
 * @param string       $subject Subject.
 * @param string       $body HTML body.
 * @param array        $headers Headers.
 * @param array        $attachments Attachments.
 * @return bool
 */
function leadwerk_theme_schadenfall_wp_mail( $to, $subject, $body, $headers = array(), $attachments = array() ) {
	$headers = array_merge(
		array(
			'From: ' . leadwerk_theme_schadenfall_sender_name() . ' <' . leadwerk_theme_schadenfall_sender_email() . '>',
			'Content-Type: text/html; charset=UTF-8',
		),
		(array) $headers
	);

	add_filter( 'wp_mail_from', 'leadwerk_theme_schadenfall_mail_from', 20 );
	add_filter( 'wp_mail_from_name', 'leadwerk_theme_schadenfall_mail_from_name', 20 );
	add_action( 'phpmailer_init', 'leadwerk_theme_schadenfall_configure_phpmailer', 20 );

	try {
		return (bool) wp_mail( $to, $subject, $body, $headers, $attachments );
	} finally {
		remove_action( 'phpmailer_init', 'leadwerk_theme_schadenfall_configure_phpmailer', 20 );
		remove_filter( 'wp_mail_from_name', 'leadwerk_theme_schadenfall_mail_from_name', 20 );
		remove_filter( 'wp_mail_from', 'leadwerk_theme_schadenfall_mail_from', 20 );
	}
}

/**
 * Field definitions shared by the frontend form and email templates.
 *
 * @return array<int,array<string,mixed>>
 */
function leadwerk_theme_schadenfall_fields() {
	return array(
		array(
			'key'         => 'name_versicherungsnehmer',
			'label'       => 'Name Versicherungsnehmer',
			'required'    => true,
			'type'        => 'text',
			'placeholder' => 'Name Versicherungsnehmer*',
		),
		array(
			'key'         => 'email',
			'label'       => 'E-Mail',
			'required'    => true,
			'type'        => 'email',
			'placeholder' => 'E-Mail*',
		),
		array(
			'key'         => 'datum_uhrzeit_ort',
			'label'       => 'Datum, Uhrzeit und Ort des Schadens',
			'required'    => true,
			'type'        => 'textarea',
			'placeholder' => 'Datum, Uhrzeit und Ort (genaue Adresse) des Schadens*',
		),
		array(
			'key'         => 'genaue_schilderung',
			'label'       => 'Genaue Schilderung',
			'required'    => true,
			'type'        => 'textarea',
			'placeholder' => 'Genaue Schilderung, was passiert ist (3-10 Saetze) und die Ursache (z.B. Rohrbruch)*',
		),
		array(
			'key'         => 'geschaedigte_beteiligte_zeugen',
			'label'       => 'Geschaedigte, Beteiligte und Zeugen',
			'required'    => true,
			'type'        => 'textarea',
			'placeholder' => 'Name, Adresse, Telefonnummer & E-Mail des Geschaedigten und weiteren Beteiligten Personen/Zeugen.*',
		),
		array(
			'key'         => 'polizeiliche_meldung',
			'label'       => 'Polizeiliche Meldung',
			'required'    => false,
			'type'        => 'textarea',
			'placeholder' => 'Bei polizeilicher Meldung (zwingend bei Diebstahl) das Aktenzeichen und die Kontaktstelle der Polizei.',
		),
		array(
			'key'         => 'beschaedigter_gegenstand_oder_verletzung',
			'label'       => 'Beschaedigter Gegenstand oder Verletzung',
			'required'    => true,
			'type'        => 'textarea',
			'placeholder' => 'Beschreibung des beschaedigten Gegenstands (z.B. iPhone 16 - 128GB) oder der Verletzung.*',
		),
		array(
			'key'         => 'geschaetzte_schadenhoehe',
			'label'       => 'Geschaetzte Schadenhoehe in EUR',
			'required'    => false,
			'type'        => 'number',
			'placeholder' => 'Geschaetzte Schadenhoehe in EUR',
		),
		array(
			'key'         => 'kfz_schaeden',
			'label'       => 'Bei KFZ Schaeden',
			'required'    => false,
			'type'        => 'textarea',
			'placeholder' => 'Bei KFZ Schaeden: Marke, Modell, Kennzeichen der geschaedigten Fahrzeuge.',
		),
	);
}

/**
 * Build the standalone form when WPForms is not configured.
 *
 * @param string $fallback_html Imported static form fallback.
 * @return string
 */
function leadwerk_theme_get_schadenfall_standalone_form_markup( $fallback_html = '' ) {
	$challenge = leadwerk_theme_schadenfall_new_challenge();
	$notice    = leadwerk_theme_schadenfall_status_notice();
	$started   = time();

	$fields = '';
	foreach ( leadwerk_theme_schadenfall_fields() as $field ) {
		$key         = (string) $field['key'];
		$label       = (string) $field['label'];
		$type        = (string) $field['type'];
		$placeholder = (string) $field['placeholder'];
		$required    = ! empty( $field['required'] ) ? ' required' : '';
		$required_ui = ! empty( $field['required'] ) ? '*' : '';

		if ( 'textarea' === $type ) {
			$fields .= sprintf(
				'<div class="form-group"><label class="form-label" for="schadenfall-%1$s">%2$s%3$s</label><textarea class="form-textarea" id="schadenfall-%1$s" name="%1$s" placeholder="%4$s" rows="4"%5$s></textarea></div>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_html( $required_ui ),
				esc_attr( $placeholder ),
				$required
			);
			continue;
		}

		$extra = '';
		if ( 'number' === $type ) {
			$extra = ' inputmode="decimal" min="0" step="0.01"';
		}

		$fields .= sprintf(
			'<div class="form-group"><label class="form-label" for="schadenfall-%1$s">%2$s%3$s</label><input class="form-input" id="schadenfall-%1$s" name="%1$s" placeholder="%4$s" type="%5$s"%6$s%7$s/></div>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_html( $required_ui ),
			esc_attr( $placeholder ),
			esc_attr( $type ),
			$extra,
			$required
		);
	}

	$nonce = wp_nonce_field( LEADWERK_SCHADENFALL_ACTION, 'leadwerk_schadenfall_nonce', false, false );
	$form  = '<div class="leadwerk-contact-form-slot leadwerk-contact-form-slot--schadenfall">';
	$form .= $notice;
	$form .= '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="POST" enctype="multipart/form-data" data-validate data-form-purpose="schadenfall" id="schadenfall-form">';
	$form .= '<input type="hidden" name="action" value="' . esc_attr( LEADWERK_SCHADENFALL_ACTION ) . '"/>';
	$form .= '<input type="hidden" name="leadwerk_schadenfall_started" value="' . esc_attr( (string) $started ) . '"/>';
	$form .= '<input type="hidden" name="leadwerk_schadenfall_challenge_token" value="' . esc_attr( $challenge['token'] ) . '"/>';
	$form .= $nonce;
	$form .= '<div class="form-group form-honeypot" aria-hidden="true"><label for="schadenfall-website">Website</label><input id="schadenfall-website" name="website" tabindex="-1" autocomplete="off" type="text"/></div>';
	$form .= $fields;
	$form .= '<div class="form-group"><label class="form-label" for="schadenfall-files">PDF-Dateien hochladen</label><input accept="application/pdf,.pdf" class="form-input form-input-file" id="schadenfall-files" name="attachments[]" multiple type="file"/><p class="form-hint">Bitte nur PDF-Dateien hochladen. Die Dateien werden als E-Mail-Anhang verarbeitet und nicht auf der Website gespeichert.</p></div>';
	$form .= '<div class="form-group schadenfall-security-question"><label class="form-label" for="schadenfall-security">Sicherheitsfrage: ' . esc_html( $challenge['question'] ) . '</label><input class="form-input" id="schadenfall-security" name="leadwerk_schadenfall_challenge_answer" inputmode="numeric" pattern="[0-9]*" required type="text"/><p class="form-hint">Schutz gegen automatische Formularsendungen.</p></div>';
	$form .= '<p class="form-hint schadenfall-security-note">Nach dem Absenden erhaeltst Du eine kurze Eingangsbest&auml;tigung per E-Mail. Es wird keine weitere Funktion auf der Website ausgeloest.</p>';
	$form .= '<button class="btn btn-primary btn-lg btn-full" type="submit">Senden</button>';
	$form .= '</form></div>';

	return '' !== trim( $form ) ? $form : (string) $fallback_html;
}

/**
 * Status notice after redirect.
 *
 * @return string
 */
function leadwerk_theme_schadenfall_status_notice() {
	if ( empty( $_GET['schadenfall_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only notice.
		return '';
	}

	$status = sanitize_key( wp_unslash( $_GET['schadenfall_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only notice.
	$code   = isset( $_GET['schadenfall_code'] ) ? sanitize_key( wp_unslash( $_GET['schadenfall_code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only notice.

	if ( 'success' === $status ) {
		return '<div class="form-status form-status-success" role="status"><strong>Danke, die Schadenmeldung wurde uebermittelt.</strong><span>Du erhaeltst eine kurze Eingangsbest&auml;tigung per E-Mail.</span></div>';
	}

	$messages = array(
		'security'     => 'Die Sicherheitspruefung ist fehlgeschlagen. Bitte lade die Seite neu und sende das Formular erneut.',
		'required'     => 'Bitte fuelle alle Pflichtfelder aus.',
		'email'        => 'Bitte gib eine gueltige E-Mail-Adresse ein.',
		'rate_limit'   => 'Aus Sicherheitsgruenden sind gerade zu viele Anfragen eingegangen. Bitte versuche es spaeter erneut.',
		'file_type'    => 'Bitte lade ausschliesslich PDF-Dateien hoch.',
		'file_size'    => 'Eine PDF-Datei ist zu gross.',
		'file_upload'  => 'Eine Datei konnte nicht verarbeitet werden.',
		'mail_failed'  => 'Die E-Mail konnte nicht versendet werden. Bitte versuche es erneut oder schreibe direkt an finanzen@ludwigoelze.com.',
		'server_error' => 'Es gab ein technisches Problem. Bitte versuche es erneut.',
	);
	$message = $messages[ $code ] ?? $messages['server_error'];

	return '<div class="form-status form-status-error" role="alert"><strong>Die Schadenmeldung wurde nicht gesendet.</strong><span>' . esc_html( $message ) . '</span></div>';
}

/**
 * Create a signed arithmetic challenge.
 *
 * @return array{question:string,token:string}
 */
function leadwerk_theme_schadenfall_new_challenge() {
	$a      = wp_rand( 2, 8 );
	$b      = wp_rand( 1, 7 );
	$exp    = time() + 2 * HOUR_IN_SECONDS;
	$answer = $a + $b;
	$data   = array(
		'a'   => $a,
		'b'   => $b,
		'exp' => $exp,
	);
	$json   = wp_json_encode( $data );
	$sig    = hash_hmac( 'sha256', (string) $json . '|' . (string) $answer, leadwerk_theme_schadenfall_hmac_key() );
	$token  = leadwerk_theme_schadenfall_base64url_encode( (string) $json ) . '.' . $sig;

	return array(
		'question' => $a . ' + ' . $b . ' = ?',
		'token'    => $token,
	);
}

/**
 * Validate arithmetic challenge.
 *
 * @param string $token Token.
 * @param string $answer User answer.
 * @return bool
 */
function leadwerk_theme_schadenfall_validate_challenge( $token, $answer ) {
	$parts = explode( '.', (string) $token, 2 );
	if ( count( $parts ) !== 2 ) {
		return false;
	}

	$json = leadwerk_theme_schadenfall_base64url_decode( $parts[0] );
	if ( '' === $json ) {
		return false;
	}

	$data = json_decode( $json, true );
	if ( ! is_array( $data ) || empty( $data['exp'] ) || (int) $data['exp'] < time() ) {
		return false;
	}

	$expected_answer = (int) $data['a'] + (int) $data['b'];
	$expected_sig    = hash_hmac( 'sha256', $json . '|' . (string) $expected_answer, leadwerk_theme_schadenfall_hmac_key() );
	if ( ! hash_equals( $expected_sig, (string) $parts[1] ) ) {
		return false;
	}

	return (int) trim( (string) $answer ) === $expected_answer;
}

/**
 * Handle frontend submission.
 *
 * @return void
 */
function leadwerk_theme_handle_schadenfall_submit() {
	if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
		leadwerk_theme_schadenfall_fail_redirect( 'security' );
	}

	if (
		empty( $_POST['leadwerk_schadenfall_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['leadwerk_schadenfall_nonce'] ) ), LEADWERK_SCHADENFALL_ACTION )
	) {
		leadwerk_theme_schadenfall_fail_redirect( 'security' );
	}

	if ( ! empty( $_POST['website'] ) ) {
		leadwerk_theme_schadenfall_fail_redirect( 'security' );
	}

	$started = isset( $_POST['leadwerk_schadenfall_started'] ) ? absint( wp_unslash( $_POST['leadwerk_schadenfall_started'] ) ) : 0;
	if ( ! $started || time() - $started < 2 || time() - $started > DAY_IN_SECONDS ) {
		leadwerk_theme_schadenfall_fail_redirect( 'security' );
	}

	$challenge_token  = isset( $_POST['leadwerk_schadenfall_challenge_token'] ) ? sanitize_text_field( wp_unslash( $_POST['leadwerk_schadenfall_challenge_token'] ) ) : '';
	$challenge_answer = isset( $_POST['leadwerk_schadenfall_challenge_answer'] ) ? sanitize_text_field( wp_unslash( $_POST['leadwerk_schadenfall_challenge_answer'] ) ) : '';
	if ( ! leadwerk_theme_schadenfall_validate_challenge( $challenge_token, $challenge_answer ) ) {
		leadwerk_theme_schadenfall_fail_redirect( 'security' );
	}

	$fields = leadwerk_theme_schadenfall_collect_fields();
	if ( is_wp_error( $fields ) ) {
		leadwerk_theme_schadenfall_fail_redirect( $fields->get_error_code() );
	}

	if ( ! leadwerk_theme_schadenfall_rate_limit_ok( (string) $fields['email'] ) ) {
		leadwerk_theme_schadenfall_fail_redirect( 'rate_limit' );
	}

	$submission_id = gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 10, false, false );
	$mail_files    = leadwerk_theme_schadenfall_prepare_uploaded_files( $submission_id );
	if ( is_wp_error( $mail_files ) ) {
		leadwerk_theme_schadenfall_fail_redirect( $mail_files->get_error_code() );
	}

	$mail_sent = leadwerk_theme_schadenfall_send_owner_mail( $fields, $mail_files, $submission_id );
	leadwerk_theme_schadenfall_cleanup_mail_attachments( $mail_files );
	if ( ! $mail_sent ) {
		leadwerk_theme_schadenfall_fail_redirect( 'mail_failed' );
	}

	leadwerk_theme_schadenfall_send_customer_mail( $fields, $submission_id );
	leadwerk_theme_schadenfall_success_redirect();
}
add_action( 'admin_post_' . LEADWERK_SCHADENFALL_ACTION, 'leadwerk_theme_handle_schadenfall_submit' );
add_action( 'admin_post_nopriv_' . LEADWERK_SCHADENFALL_ACTION, 'leadwerk_theme_handle_schadenfall_submit' );

/**
 * Collect and sanitize form fields.
 *
 * @return array<string,string>|WP_Error
 */
function leadwerk_theme_schadenfall_collect_fields() {
	$values = array();
	foreach ( leadwerk_theme_schadenfall_fields() as $field ) {
		$key = (string) $field['key'];
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		if ( 'email' === $key ) {
			$value = sanitize_email( $raw );
			if ( ! is_email( $value ) ) {
				return new WP_Error( 'email' );
			}
		} elseif ( 'geschaetzte_schadenhoehe' === $key ) {
			$value = preg_replace( '/[^0-9,.]/', '', (string) $raw );
		} else {
			$value = sanitize_textarea_field( $raw );
		}

		if ( ! empty( $field['required'] ) && '' === trim( (string) $value ) ) {
			return new WP_Error( 'required' );
		}
		$values[ $key ] = (string) $value;
	}

	return $values;
}

/**
 * Lightweight rate limit per IP and email.
 *
 * @param string $email Submitted email.
 * @return bool
 */
function leadwerk_theme_schadenfall_rate_limit_ok( $email ) {
	$key   = 'lw_schadenfall_' . hash_hmac( 'sha256', leadwerk_theme_schadenfall_client_ip() . '|' . strtolower( $email ), leadwerk_theme_schadenfall_hmac_key() );
	$count = (int) get_transient( $key );
	if ( $count >= (int) apply_filters( 'leadwerk_schadenfall_rate_limit_per_hour', 5 ) ) {
		return false;
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	return true;
}

/**
 * Normalize uploaded files array.
 *
 * @return array<int,array<string,mixed>>
 */
function leadwerk_theme_schadenfall_normalize_files() {
	if ( empty( $_FILES['attachments'] ) || ! is_array( $_FILES['attachments'] ) ) {
		return array();
	}

	$files = $_FILES['attachments']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per file below.
	if ( ! is_array( $files['name'] ?? null ) ) {
		return array( $files );
	}

	$normalized = array();
	foreach ( array_keys( $files['name'] ) as $index ) {
		$normalized[] = array(
			'name'     => $files['name'][ $index ] ?? '',
			'type'     => $files['type'][ $index ] ?? '',
			'tmp_name' => $files['tmp_name'][ $index ] ?? '',
			'error'    => $files['error'][ $index ] ?? UPLOAD_ERR_NO_FILE,
			'size'     => $files['size'][ $index ] ?? 0,
		);
	}

	return $normalized;
}

/**
 * Validate uploaded PDFs and prepare temporary mail attachments.
 *
 * @param string $submission_id Submission ID.
 * @return array<int,array<string,mixed>>|WP_Error
 */
function leadwerk_theme_schadenfall_prepare_uploaded_files( $submission_id ) {
	$files     = leadwerk_theme_schadenfall_normalize_files();
	$prepared  = array();
	$max_files = (int) apply_filters( 'leadwerk_schadenfall_max_pdf_files', 8 );
	$max_size  = (int) apply_filters( 'leadwerk_schadenfall_max_pdf_size', 12 * MB_IN_BYTES );
	$count     = 0;

	foreach ( $files as $file ) {
		$error = (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE );
		if ( UPLOAD_ERR_NO_FILE === $error ) {
			continue;
		}
		$count++;
		if ( $count > $max_files ) {
			return leadwerk_theme_schadenfall_upload_error( $prepared, 'file_upload' );
		}
		if ( UPLOAD_ERR_OK !== $error ) {
			return leadwerk_theme_schadenfall_upload_error( $prepared, 'file_upload' );
		}
		$tmp = (string) ( $file['tmp_name'] ?? '' );
		if ( ! is_uploaded_file( $tmp ) || ! is_readable( $tmp ) ) {
			return leadwerk_theme_schadenfall_upload_error( $prepared, 'file_upload' );
		}
		$size = (int) ( $file['size'] ?? 0 );
		if ( $size <= 0 || $size > $max_size ) {
			return leadwerk_theme_schadenfall_upload_error( $prepared, 'file_size' );
		}
		$original = sanitize_file_name( (string) ( $file['name'] ?? 'schadenfall.pdf' ) );
		if ( '' === $original ) {
			$original = 'schadenfall.pdf';
		}
		if ( '.pdf' !== strtolower( substr( $original, -4 ) ) || ! leadwerk_theme_schadenfall_tmp_is_pdf( $tmp ) ) {
			return leadwerk_theme_schadenfall_upload_error( $prepared, 'file_type' );
		}

		$file_id  = wp_generate_uuid4();
		$hash     = hash_file( 'sha256', $tmp );
		$mail_path = leadwerk_theme_schadenfall_prepare_mail_attachment( $tmp, $submission_id, $file_id, $original );
		if ( is_wp_error( $mail_path ) ) {
			leadwerk_theme_schadenfall_cleanup_mail_attachments( $prepared );
			return $mail_path;
		}
		$prepared[] = array(
			'id'            => $file_id,
			'original_name' => $original,
			'size'          => $size,
			'sha256'        => $hash,
			'_mail_path'    => $mail_path,
		);
	}

	return $prepared;
}

/**
 * Clean prepared attachment files and return an upload error.
 *
 * @param array<int,array<string,mixed>> $files Prepared files.
 * @param string                         $code Error code.
 * @return WP_Error
 */
function leadwerk_theme_schadenfall_upload_error( $files, $code ) {
	leadwerk_theme_schadenfall_cleanup_mail_attachments( $files );

	return new WP_Error( $code );
}

/**
 * Copy uploaded PDF to a temporary path with a readable filename for wp_mail().
 *
 * @param string $tmp Uploaded temp path.
 * @param string $submission_id Submission ID.
 * @param string $file_id File ID.
 * @param string $original Original filename.
 * @return string|WP_Error
 */
function leadwerk_theme_schadenfall_prepare_mail_attachment( $tmp, $submission_id, $file_id, $original ) {
	$temp_dir = trailingslashit( get_temp_dir() );
	$name     = sanitize_file_name( $submission_id . '-' . $file_id . '-' . $original );
	if ( '' === $name || '.pdf' !== strtolower( substr( $name, -4 ) ) ) {
		$name .= '.pdf';
	}
	$dest = $temp_dir . $name;
	if ( ! copy( $tmp, $dest ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy -- temporary mail attachment copy.
		return new WP_Error( 'file_upload' );
	}
	@chmod( $dest, 0600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- best-effort temp permission hardening.

	return $dest;
}

/**
 * Collect temporary PDF attachment paths.
 *
 * @param array<int,array<string,mixed>> $files Prepared files.
 * @return array<int,string>
 */
function leadwerk_theme_schadenfall_mail_attachment_paths( $files ) {
	$attachments = array();
	foreach ( $files as $file ) {
		$path = is_array( $file ) ? (string) ( $file['_mail_path'] ?? '' ) : '';
		if ( '' !== $path && is_readable( $path ) ) {
			$attachments[] = $path;
		}
	}

	return $attachments;
}

/**
 * Delete temporary mail attachment copies.
 *
 * @param array<int,array<string,mixed>> $files Prepared files.
 * @return void
 */
function leadwerk_theme_schadenfall_cleanup_mail_attachments( $files ) {
	foreach ( leadwerk_theme_schadenfall_mail_attachment_paths( $files ) as $path ) {
		@unlink( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink -- best-effort temp cleanup.
	}
}

/**
 * HMAC key.
 *
 * @return string
 */
function leadwerk_theme_schadenfall_hmac_key() {
	return wp_hash( 'leadwerk-schadenfall-hmac-v1' );
}

/**
 * Validate PDF by extension-independent content checks.
 *
 * @param string $tmp Temporary file path.
 * @return bool
 */
function leadwerk_theme_schadenfall_tmp_is_pdf( $tmp ) {
	$fh = fopen( $tmp, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen -- uploaded temp stream validation.
	if ( ! $fh ) {
		return false;
	}
	$head = fread( $fh, 5 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread -- uploaded temp stream validation.
	fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose -- uploaded temp stream validation.
	if ( '%PDF-' !== $head ) {
		return false;
	}

	if ( function_exists( 'finfo_open' ) ) {
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		if ( $finfo ) {
			$mime = finfo_file( $finfo, $tmp );
			finfo_close( $finfo );
			if ( is_string( $mime ) && ! in_array( strtolower( $mime ), array( 'application/pdf', 'application/x-pdf' ), true ) ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Send owner email.
 *
 * @param array<string,string> $fields Fields.
 * @param array<int,array<string,mixed>> $files Prepared files.
 * @param string $submission_id Submission ID.
 * @return bool
 */
function leadwerk_theme_schadenfall_send_owner_mail( $fields, $files, $submission_id ) {
	$name    = $fields['name_versicherungsnehmer'] ?? 'Unbekannt';
	$email   = $fields['email'] ?? '';
	$subject = 'Neue Schadenmeldung von ' . $name;
	$content = '<p>Es wurde eine neue Schadenmeldung ueber die Website eingereicht. Die PDF-Dateien sind dieser E-Mail beigefuegt und werden nicht auf der Website gespeichert.</p>';
	$content .= '<h2>Formulardaten</h2>' . leadwerk_theme_schadenfall_email_table( $fields );
	$content .= '<h2>PDF-Dateien</h2>' . leadwerk_theme_schadenfall_file_list_for_email( $files );
	$content .= '<h2>Sicherheit</h2><p>Vorgangs-ID: <strong>' . esc_html( $submission_id ) . '</strong><br>Die PDF-Dateien wurden nur fuer den Versand als E-Mail-Anhang verarbeitet. Temporaere Server-Kopien werden nach dem Mailversand geloescht.</p>';
	$body    = leadwerk_theme_schadenfall_email_shell( 'Neue Schadenmeldung', 'Eingaben und PDF-Dateien im Anhang', $content );
	$headers = array();
	if ( is_email( $email ) ) {
		$headers[] = 'Reply-To: ' . sanitize_text_field( $name ) . ' <' . sanitize_email( $email ) . '>';
	}

	return leadwerk_theme_schadenfall_wp_mail( leadwerk_theme_schadenfall_owner_email(), $subject, $body, $headers, leadwerk_theme_schadenfall_mail_attachment_paths( $files ) );
}

/**
 * Send customer confirmation email.
 *
 * @param array<string,string> $fields Fields.
 * @param string $submission_id Submission ID.
 * @return bool
 */
function leadwerk_theme_schadenfall_send_customer_mail( $fields, $submission_id ) {
	$email = $fields['email'] ?? '';
	if ( ! is_email( $email ) ) {
		return false;
	}
	$name    = $fields['name_versicherungsnehmer'] ?? '';
	$subject = 'Deine Schadenmeldung ist eingegangen';
	$content = '<p>Hallo ' . esc_html( $name ) . ',</p>';
	$content .= '<p>deine Schadenmeldung wurde uebermittelt. Ich bekomme eine E-Mail mit deinen Angaben und den hochgeladenen PDF-Dateien.</p>';
	$content .= '<h2>Was jetzt passiert</h2><ul><li>Die Angaben werden geprueft.</li><li>Falls Rueckfragen bestehen, melde ich mich direkt bei dir.</li><li>Bitte bewahre Originalbelege und weitere Unterlagen auf.</li></ul>';
	$content .= '<p>Vorgangs-ID: <strong>' . esc_html( $submission_id ) . '</strong></p>';
	$content .= '<p>Viele Gruesse<br>Ludwig Oelze</p>';
	$body = leadwerk_theme_schadenfall_email_shell( 'Schadenmeldung erhalten', 'Kurze Eingangsbestaetigung', $content );

	return leadwerk_theme_schadenfall_wp_mail(
		sanitize_email( $email ),
		$subject,
		$body,
		array(
			'Reply-To: ' . leadwerk_theme_schadenfall_sender_name() . ' <' . leadwerk_theme_schadenfall_sender_email() . '>',
		)
	);
}

/**
 * Branded HTML email shell.
 *
 * @param string $title Title.
 * @param string $subtitle Subtitle.
 * @param string $content Inner HTML.
 * @return string
 */
function leadwerk_theme_schadenfall_email_shell( $title, $subtitle, $content ) {
	return '<!doctype html><html><body style="margin:0;background:#f6f3ec;color:#123434;font-family:Arial,sans-serif;">'
		. '<div style="display:none;max-height:0;overflow:hidden;">' . esc_html( $subtitle ) . '</div>'
		. '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f3ec;padding:28px 12px;"><tr><td align="center">'
		. '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:720px;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid #e8ddc8;box-shadow:0 18px 50px rgba(15,51,51,.10);">'
		. '<tr><td style="background:#174f4f;padding:30px 34px;color:#ffffff;"><p style="margin:0 0 10px;color:#d8b968;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Ludwig Oelze</p><h1 style="margin:0;font-family:Georgia,serif;font-size:32px;line-height:1.15;">' . esc_html( $title ) . '</h1><p style="margin:12px 0 0;color:#dbecec;font-size:16px;">' . esc_html( $subtitle ) . '</p></td></tr>'
		. '<tr><td style="padding:32px 34px;font-size:16px;line-height:1.65;">' . $content . '</td></tr>'
		. '<tr><td style="padding:22px 34px;background:#f8f6ef;color:#607070;font-size:13px;">Diese E-Mail wurde automatisch durch das Schadenfall-Kontaktformular erzeugt.</td></tr>'
		. '</table></td></tr></table></body></html>';
}

/**
 * Email table for fields.
 *
 * @param array<string,string> $fields Fields.
 * @return string
 */
function leadwerk_theme_schadenfall_email_table( $fields ) {
	$html = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0 8px;">';
	foreach ( leadwerk_theme_schadenfall_fields() as $field ) {
		$key   = (string) $field['key'];
		$label = (string) $field['label'];
		$value = trim( (string) ( $fields[ $key ] ?? '' ) );
		if ( '' === $value ) {
			$value = '-';
		}
		$html .= '<tr><td style="width:38%;vertical-align:top;padding:12px 14px;background:#f8f6ef;border-radius:14px 0 0 14px;color:#5d6d6d;font-weight:700;">' . esc_html( $label ) . '</td><td style="vertical-align:top;padding:12px 14px;background:#fbfaf6;border-radius:0 14px 14px 0;color:#123434;">' . nl2br( esc_html( $value ) ) . '</td></tr>';
	}
	$html .= '</table>';

	return $html;
}

/**
 * File list for owner email.
 *
 * @param array<int,array<string,mixed>> $files Prepared files.
 * @return string
 */
function leadwerk_theme_schadenfall_file_list_for_email( $files ) {
	if ( empty( $files ) ) {
		return '<p>Es wurden keine PDF-Dateien hochgeladen.</p>';
	}

	$attachment_count = count( leadwerk_theme_schadenfall_mail_attachment_paths( $files ) );
	$intro = $attachment_count > 0
		? '<p>Die PDF-Dateien sind dieser E-Mail als Anhang beigefuegt.</p>'
		: '<p>Die PDF-Dateien konnten nicht als Anhang vorbereitet werden.</p>';
	$html = '<ul style="padding-left:0;list-style:none;margin:0;">';
	foreach ( $files as $file ) {
		$name = (string) ( $file['original_name'] ?? 'schadenfall.pdf' );
		$size = size_format( (int) ( $file['size'] ?? 0 ), 1 );
		$html .= '<li style="margin:0 0 12px;padding:14px 16px;background:#f8f6ef;border-radius:16px;border:1px solid #e8ddc8;">'
			. '<strong>' . esc_html( $name ) . '</strong><br><span style="color:#607070;">' . esc_html( $size ) . ' | als E-Mail-Anhang beigefuegt</span>';
		$html .= '</li>';
	}
	$html .= '</ul>';

	return $intro . $html;
}

/**
 * Redirect helpers.
 *
 * @return string
 */
function leadwerk_theme_schadenfall_redirect_base() {
	$referer = wp_get_referer();
	if ( ! $referer ) {
		$referer = home_url( '/schadenfall/' );
	}
	$referer = remove_query_arg( array( 'schadenfall_status', 'schadenfall_code' ), $referer );

	return $referer;
}

/**
 * Redirect with error.
 *
 * @param string $code Error code.
 * @return never
 */
function leadwerk_theme_schadenfall_fail_redirect( $code ) {
	wp_safe_redirect( add_query_arg( array( 'schadenfall_status' => 'error', 'schadenfall_code' => sanitize_key( $code ) ), leadwerk_theme_schadenfall_redirect_base() ) . '#schadenfall-form' );
	exit;
}

/**
 * Redirect with success.
 *
 * @return never
 */
function leadwerk_theme_schadenfall_success_redirect() {
	wp_safe_redirect( add_query_arg( array( 'schadenfall_status' => 'success' ), leadwerk_theme_schadenfall_redirect_base() ) . '#schadenfall-form' );
	exit;
}

/**
 * Client IP for rate limiting.
 *
 * @return string
 */
function leadwerk_theme_schadenfall_client_ip() {
	$ip = (string) ( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' );

	return preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
}

/**
 * Base64url encode.
 *
 * @param string $value Raw value.
 * @return string
 */
function leadwerk_theme_schadenfall_base64url_encode( $value ) {
	return rtrim( strtr( base64_encode( (string) $value ), '+/', '-_' ), '=' );
}

/**
 * Base64url decode.
 *
 * @param string $value Encoded value.
 * @return string
 */
function leadwerk_theme_schadenfall_base64url_decode( $value ) {
	$value = (string) $value;
	$pad   = strlen( $value ) % 4;
	if ( $pad ) {
		$value .= str_repeat( '=', 4 - $pad );
	}
	$decoded = base64_decode( strtr( $value, '-_', '+/' ), true );

	return false === $decoded ? '' : $decoded;
}
