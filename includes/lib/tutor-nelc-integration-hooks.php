<?php
use tutorLmsLrsPlugin\includes\Interactions\XapiIntegration;

function event_enabled($event_key) {
    $option = get_option('lmtni_' . $event_key);
    return $option === 'on' || $option === 1 || $option === true;
}


function course_integrate_status($course_id) {
    $course_itegrate = get_option('lmtni_xapi_courses_integrate');
    $link_status = get_post_meta($course_id, 'tutor_nelc_integration_link_course', true) === 'on' ? 'checked' : '';

    if ($link_status != 'checked' && !$course_itegrate) {
        return false;
    }

    return true;
}

//////////////////////////////////
add_action('tutor_after_enroll', 'nelec_register_statemente_tutor');
function nelec_register_statemente_tutor ( $course_id )
{

    if (!course_integrate_status($course_id) || !event_enabled('xapi_event_registered')) {
        return;
    }
    global $post;
    $user = wp_get_current_user();

    // Get student info
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );
    $usName = $user->display_name;
    $usNID = empty($ntd) || $ntd == '' ? $usName : $ntd;
    $usEmail = $user->user_email;
    $learneMobileNo = get_user_meta($user->ID, 'phone_number', true);
    $learnerFullName = get_user_meta($user->ID, 'first_name', true) . ' ' . get_user_meta($user->ID, 'last_name', true);
    $learnerFullName = empty($learnerFullName) || $learnerFullName == '' ? $usName : $learnerFullName;
    $learnerNationality = get_user_meta($user->ID, 'nationality', true);
    $dateOfBirth = get_user_meta($user->ID, 'date_of_birth', true);


    
    // Get author info
    $author_id = $post->post_author;
	$instructor = get_userdata($author_id);

    $instName = $instructor->display_name;
    $instEmail = $instructor->user_email;
    // Get course info
    $course = get_post( $course_id );
    $courseName = sanitize_text_field($course->post_title);
    $courseDesc = strip_tags($course->post_content);
    $courseLang = get_post_meta($course_id, '_nelc_course_language', true);
    
    // استخدام القيم الافتراضية إذا كانت فارغة
        $duration = get_post_meta($course_id, '_nelc_course_duration', true);
    if (empty($duration)) {
        $duration = get_post_meta($course_id, '_course_duration', true);

        if ($duration) {
            $duration_data = maybe_unserialize($duration);

            $hours = isset($duration_data['hours']) ? intval($duration_data['hours']) : 0;
            $minutes = isset($duration_data['minutes']) ? intval($duration_data['minutes']) : 0;
            $seconds = 0;

            // التنسيق على شكل PTxxHxxMxxS
            $duration = sprintf('PT%02dH%02dM%02dS', $hours, $minutes, $seconds);
        } else {
            $duration = 'PT00H00M00S'; // في حال لم توجد قيمة
        }
    }
    
    if (empty($courseLang)) {
        $courseLang = 'en-US';
    }

    $xapiSender = new XapiIntegration;
    $response = $xapiSender->Registered([
        'name' => $usNID,
        'email' => $usEmail,
        'learneMobileNo' => $learneMobileNo,
        'learnerFullName' => $learnerFullName,
        'learnerNationality' => $learnerNationality,
        'dateOfBirth' => $dateOfBirth,
        'instructor' => $instName,
        'inst_email' => $instEmail,
        'courseId' => $course_id,
        'courseName' => $courseName,
        'courseDesc' => $courseDesc,
        'duration' => $duration,
        'courseLang' => $courseLang,
    ]);

    if (!empty($response) || !is_wp_error($response)) {
        if (isset($response['http_code'])) {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['response']);
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    } else {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }
    
}

//add_action('tutor/lesson_list/before/topic', 'nelec_initialize_statemente_tutor');
add_action('tutor/course/started', 'nelec_initialize_statemente_tutor', 10, 2);
add_action('tutor_course_start_before', 'nelec_initialize_statemente_tutor');
function nelec_initialize_statemente_tutor ( $course_id ){

    if (!course_integrate_status($course_id) || !event_enabled('xapi_event_initialized')) {
        return;
    }

    global $post;
    $user = wp_get_current_user();

    // Get student info
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );
    $usName = $user->display_name;
    $usNID = empty($ntd) || $ntd == '' ? $usName : $ntd;
    $usEmail = $user->user_email;

    
    // Get author info
    $author_id = $post->post_author;
	$instructor = get_userdata($author_id);
    $instName = $instructor->display_name;
    $instEmail = $instructor->user_email;
    
    // Get course info
    $course = get_post( $course_id );
    $courseName = sanitize_text_field($course->post_title);
    $courseDesc = strip_tags($course->post_content);
    $duration = get_post_meta($course_id, '_nelc_course_duration', true);
    $courseLang = get_post_meta($course_id, '_nelc_course_language', true);
    
    // استخدام القيم الافتراضية إذا كانت فارغة
    if (empty($duration)) {
        $duration = 'PT50H00M00S';
    }
    
    if (empty($courseLang)) {
        $courseLang = 'en-US';
    }

    $xapiSender = new XapiIntegration;
    $response = $xapiSender->Initialized([
        'name' => $usNID,
        'email' => $usEmail,
        'instructor' => $instName,
        'inst_email' => $instEmail,
        'courseId' => $course_id,
        'courseName' => $courseName,
        'courseDesc' => '',
        'courseLang' => $courseLang,
    ]);

    if (!empty($response) || !is_wp_error($response)) {
        if (isset($response['http_code'])) {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['response']);
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    } else {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }
}

add_action('tutor_lesson_completed_after', 'lesson_completed_hook');
function lesson_completed_hook($lesson_id) {

    $lesson = get_post( $lesson_id );
    $course_id = tutor_utils()->get_course_id_by_lesson($lesson_id);
    $course = get_post( $course_id );

    if (!course_integrate_status($course_id)) {
        return;
    }

    // منع إرسال الحدث إذا لم يكن فعالاً
    $send_completed = event_enabled('xapi_event_completed_lesson');
    $send_progressed = event_enabled('xapi_event_progressed');
    $send_completed_unit = event_enabled('xapi_event_completed_unit');

    $user = wp_get_current_user();
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );
    $usName = $user->display_name;
    $usNID = empty($ntd) || $ntd == '' ? $usName : $ntd;
    $usEmail = $user->user_email;

    // Get author info
    $author_id = $course->post_author;
	$instructor = get_userdata($author_id);
    $instName = $instructor->display_name;
    $instEmail = $instructor->user_email;

    // Get course info
    $courseName = sanitize_text_field($course->post_title);
    $courseDesc = strip_tags($course->post_content);
    $courseLang = get_post_meta($course_id, '_nelc_course_language', true);
    if (empty($courseLang)) {
        $courseLang = 'en-US';
    }

    $percentage = tutor_utils()->get_course_completed_percent( $course_id, $user->ID );
    $scaled = $percentage / 100;

    // Get lesson info
    $lessonName = sanitize_text_field($lesson->post_title);
    $lessonDesc = strip_tags($lesson->post_content);
    $lessonDuration = get_post_meta($lesson->ID, '_lesson_duration', true);
    $hours = 0;
    $minutes = 0;
    if (isset($lessonDuration) && $lessonDuration > 1) {
        $hours = floor($lessonDuration / 60);
        $minutes = $lessonDuration % 60;
    }
    // تنسيق المدة
    $lessonDuration = sprintf('PT%02dH%02dM00S', $hours, $minutes);
    // Send Lesson completed
    if ($send_completed) {
        $xapiSender = new XapiIntegration;
        $response = $xapiSender->Completed([
                'name' => $usNID,
                'email' => $usEmail,
                'lessonUrl'=> $lesson->guid ?: get_permalink($lesson->ID),
                'lessonName'=> $lessonName,
                'lessonDesc'=> $lessonDesc,
                'instructor' => $instName,
                'inst_email' => $instEmail,
                'courseId' => $course->ID,
                'courseName' => $courseName,
                'courseDesc' => '',
                'courseLang' => $courseLang,
                'lessonDuration' => $lessonDuration,
            ]);

        if (!empty($response) || !is_wp_error($response)) {
            if (isset($response['http_code'])) {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['response']);
            } else {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
            }
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    }

    // Send Progressed
    if ($send_progressed) {
        $xapiSender1 = new XapiIntegration;
        $response1 = $xapiSender1->Progressed([
        'name' => $usNID,
        'email' => $usEmail,
        'courseId' => $course->ID,
        'courseName' => $courseName,
        'courseDesc' => '',
        'instructor' => $instName,
        'inst_email' => $instEmail,
        'scaled' => round($scaled, 2),
        'completion' => $percentage == 100 ? true : false,
        'courseLang' => $courseLang,
    ]);
        if (!empty($response1) || !is_wp_error($response1)) {
            if (isset($response1['http_code'])) {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response1['response']);
            } else {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
            }
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    }


    $topic_id = $lesson->post_parent;
    
    if (!$topic_id) {
        return false;
    }

    $topic = get_post($topic_id);
    if (!$topic) {
        return false;
    }

    $is_unit_completed = check_student_completed_unit($user->ID, $topic_id);

    if( $is_unit_completed && $send_completed_unit ){
        $unitName = sanitize_text_field($topic->post_title);
        $unitDesc = strip_tags($topic->post_content);
        $xapiSender2 = new XapiIntegration;
        $response2 = $xapiSender2->CompletedUnit([
            'name' => $usNID,
            'email' => $usEmail,
            'unitUrl'=> get_site_url() . '/course/unit/view.php?id='.$topic_id,
            'unitName'=> $unitName,
            'unitDesc'=> $unitDesc,
            'instructor' => $instName,
            'inst_email' => $instEmail,
            'courseId' => $course->ID,
            'courseName' => $courseName,
            'courseDesc' => '',
            'courseLang' => $courseLang,
        ]);
        if (!empty($response2) || !is_wp_error($response2)) {
            if (isset($response2['http_code'])) {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response2['response']);
            } else {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
            }
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    }

}

add_action('tutor_quiz/attempt_ended', 'quiz_attempt_hook');
function quiz_attempt_hook($attempt_id) {
    $attempt_data = tutor_utils()->get_attempt($attempt_id);
    
    $user =  wp_get_current_user();
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );
    $usName = $user->display_name;
    $usNID = empty($ntd) || $ntd == '' ? $usName : $ntd;
    $usEmail = $user->user_email;
    $quiz_id = tutor_utils()->avalue_dot('quiz_id', $attempt_data);
    $quiz_data = get_post($quiz_id);

    $course_id = tutor_utils()->avalue_dot('course_id', $attempt_data);
    $course = get_post($course_id);
    if (!course_integrate_status($course_id) || !event_enabled('xapi_event_attempted')) {
        return;
    }
    $courseName = sanitize_text_field($course->post_title);
    $courseDesc = strip_tags($course->post_content);
    $courseLang = get_post_meta($course_id, '_nelc_course_language', true);
    if (empty($courseLang)) {
        $courseLang = 'en-US';
    }


    $author_id = $course->post_author;
    $instructor = get_userdata($author_id);
    $instName = $instructor->display_name;
    $instEmail = $instructor->user_email;

    $points = tutor_utils()->avalue_dot('earned_marks', $attempt_data);
    $total_points = tutor_utils()->avalue_dot('total_marks', $attempt_data);
        // استخراج passing grade من attempt_info
    $attempt_info = maybe_unserialize($attempt_data->attempt_info);
    $passing_grade = isset($attempt_info['passing_grade']) ? floatval($attempt_info['passing_grade']) : 0;

    // حساب النسبة المئوية
    $percentage = ($points / $total_points) * 100;
    // تحديد إذا كانت المحاولة ناجحة بناءً على passing grade
    $is_passed = $percentage >= $passing_grade;
    $min = ($passing_grade / 100) * $total_points;

    function get_completed_mmm( int $course_id, int $student_id ): int {
		global $wpdb;
		$course_id  = sanitize_text_field( $course_id );
		$student_id = sanitize_text_field( $student_id );
		$count      = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT attempt_id) AS total
				FROM {$wpdb->prefix}tutor_quiz_attempts
				WHERE quiz_id = %d
				AND user_id = %d
				AND attempt_status = %s
			",
				$course_id,
				$student_id,
				'attempt_ended'
			)
		);
		return (int) $count;
	}

    $attempt_count = get_completed_mmm($quiz_id, $user->ID );

    $xapiSender = new XapiIntegration;
    $response = $xapiSender->Attempted([
        'name' => $usNID,
        'email' => $usEmail,
        'quizUrl' => get_site_url() . '/course/quiz/view.php?id='.$quiz_id,
        'quizName' => $quiz_data->post_title,
        'quizDesc' => strip_tags($quiz_data->post_content),
        'instructor' => $instName,
        'inst_email' => $instEmail,
        'attempNumber' => $attempt_count,
        'courseId' => $course_id,
        'courseName' => $courseName,
        'courseDesc' => '',
        'courseLang' => $courseLang,
        'scaled' => round($percentage / 100, 2),
        'raw' => (float) $points,
        'min' => (float) $min,
        'max' => (float) $total_points,
        'completion' => true,
        'success' => $is_passed == 1 ? true : false,
    ]);
    if (!empty($response) || !is_wp_error($response)) {
        if (isset($response['http_code'])) {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['response']);
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    } else {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }
    
}

add_action('tutor_course_complete_after', 'course_completed_hook', 10, 2);
function course_completed_hook($course_id) {
    if (!course_integrate_status($course_id) || !event_enabled('xapi_event_completed_course')) {
        return;
    }
    $user = wp_get_current_user();
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );
    $usName = $user->display_name;
    $usNID = empty($ntd) || $ntd == '' ? $usName : $ntd;
    $usEmail = $user->user_email;

    $course = get_post($course_id);
    $courseName = sanitize_text_field($course->post_title);
    $courseDesc = strip_tags($course->post_content);
    $courseLang = get_post_meta($course_id, '_nelc_course_language', true);
    if (empty($courseLang)) {
        $courseLang = 'en-US';
    }

    $instructor = get_userdata($course->post_author);
    $instName = $instructor->display_name;
    $instEmail = $instructor->user_email;


    // التحقق مما إذا كانت الدورة مكتملة
    $is_comp = tutor_utils()->is_completed_course($course_id);

    // الحصول على رابط الشهادة
    $certificate_link = $is_comp ? esc_url(site_url("/?cert_hash=" . $is_comp->completed_hash)) : null;

    $xapiSender = new XapiIntegration;
    $response = $xapiSender->CompletedCourse([
        'name' => $usNID,
        'email' => $usEmail,
        'courseId' => $course_id,
        'courseName' => $courseName,
        'courseDesc' => '',
        'courseLang' => $courseLang,
        'instructor' => $instName,
        'inst_email' => $instEmail,
    ]);
    if (!empty($response) || !is_wp_error($response)) {
        if (isset($response['http_code'])) {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['response']);
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    } else {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }

    if (event_enabled('xapi_event_earned')) {
        $xapiSender1 = new XapiIntegration;
        $response1 = $xapiSender1->Earned([
        'name' => $usNID,
        'email' => $usEmail,
        'certUrl' => $certificate_link,
        'certName' => "Cert: $courseName",
        'courseId' => $course_id,
        'courseName' => $courseName,
        'courseDesc' => '',
        'courseLang' => $courseLang,
    ]);
        if (!empty($response1) || !is_wp_error($response1)) {
            if (isset($response1['http_code'])) {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response1['response']);
            } else {
                update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
            }
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    }

}

add_action('tutor_after_rating_placed', 'course_rated_hook');
function course_rated_hook( $comment_id )
{

    $user = wp_get_current_user();
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );
    $usName = $user->display_name;
    $usNID = empty($ntd) || $ntd == '' ? $usName : $ntd;
    $usEmail = $user->user_email;

    $comment = get_comment( $comment_id );
    $course_id = $comment->comment_post_ID;
    $course = get_post( $course_id );
    $courseName = sanitize_text_field($course->post_title);
    $courseDesc = strip_tags($course->post_content);
    $courseLang = get_post_meta($course_id, '_nelc_course_language', true);
    if (empty($courseLang)) {
        $courseLang = 'en-US';
    }

    if (!course_integrate_status($course_id) || !event_enabled('xapi_event_rated')) {
        return;
    }

    $author_id = $course->post_author;
    $instructor = get_userdata($author_id);
    $instName = $instructor->display_name;
    $instEmail = $instructor->user_email;

    $rate_star = get_comment_meta($comment_id, 'tutor_rating', true);;
    $rate_comment = $comment->comment_content;

    $xapiSender = new XapiIntegration;
    $response = $xapiSender->Rated([
        'name' => $usNID,
        'email' => $usEmail,
        'courseId' => $course_id,
        'courseName' => $courseName,
        'courseDesc' => '',
        'courseLang' => $courseLang,
        'instructor' => $instName,
        'inst_email' => $instEmail,
        'scaled' => (float) $rate_star / 5,
        'raw' => (float) $rate_star,
        'min' => 0,
        'max' => 5,
        'comment' => $rate_comment,
    ]);
    if (!empty($response) || !is_wp_error($response)) {
        if (isset($response['http_code'])) {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['response']);
        } else {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }
    } else {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }

}

add_action('wp_ajax_mark_video_watched', 'mark_video_watched_callback');
add_action('wp_ajax_nopriv_mark_video_watched', 'mark_video_watched_callback');

function mark_video_watched_callback() {
    // التحقق من وجود البيانات المطلوبة
    if (!event_enabled('xapi_event_watched')) {
        wp_send_json_error('تم تعطيل إرسال حدث Watched من الإعدادات');
        return;
    }
    if (isset($_POST['duration']) && isset($_POST['lesson_id'])) {
        $duration = $_POST['duration'];
		$lesson_id = $_POST['lesson_id'];
		$lesson = get_post( $lesson_id );

		
		if(  $lesson && !empty($lesson) && $lesson->post_type !== 'lesson'){
			return;
		}

		$course = get_post( $lesson->post_parent );

		$user = wp_get_current_user();
		$ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );
		$usName = $user->display_name;
		$usNID = empty($ntd) || $ntd == '' ? $usName : $ntd;
		$usEmail = $user->user_email;
	
		// Get author info
		$author_id = $course->post_author;
		$instructor = get_userdata($author_id);
		$instName = $instructor->display_name;
		$instEmail = $instructor->user_email;
	
		// Get course info
		$courseName = sanitize_text_field($course->post_title);
		$courseDesc = strip_tags($course->post_content);
		$courseLang = get_post_meta($course->ID, '_nelc_course_language', true);
		if (empty($courseLang)) {
			$courseLang = 'en-US';
		}

		$lessonName = sanitize_text_field($lesson->post_title);
    	$lessonDesc = strip_tags($lesson->post_content);

		$xapiSender = new XapiIntegration;
		$response = $xapiSender->Watched([
			'name' => $usNID,
			'email' => $usEmail,
			'lessonUrl'=> get_site_url() . '/course/video/view.php?id=' . $lesson_id,
			'lessonName'=> 'video: ' . $lessonName,
			'lessonDesc'=> $lessonDesc,
			'instructor' => $instName,
			'inst_email' => $instEmail,
			'courseId' => $course->ID,
			'courseName' => $courseName,
			'courseDesc' => '',
			'courseLang' => $courseLang,
			'completion' => true,
			'duration' => $duration,
		]);

		if (!empty($response) || !is_wp_error($response)) {
			if (isset($response['http_code'])) {
				wp_send_json_success( __('The report has been sent to NELC', 'tutor-nelc-xapi') );
			} else {
				wp_send_json_error($response['response']);
			}
		} else {
			wp_send_json_error($response['response']);
		}

        
    } else {
        wp_send_json_error('بيانات غير صحيحة');
    }
}

// التحقق من إكمال الطالب للوحدة
function check_student_completed_unit($user_id, $topic_id) {

    $lesson_ids_array = get_posts(array(
        'fields'      => 'ids',
        'post_type'   => 'lesson',
        'post_parent' => $topic_id,
        'numberposts' => -1
    ));

    if (empty($lesson_ids_array)) {
        return false;
    }

    $count = 0;

    foreach ($lesson_ids_array as $lesson_id) {
        $meta_key = '_tutor_completed_lesson_id_' . $lesson_id;
        $completed = get_user_meta($user_id, $meta_key, true);

        if ( $completed && $completed != '' && !empty($completed) ) {
            $count += 1;
        }
    }

    if($count === count($lesson_ids_array)){
        
        return true;
    }

    return false;
}

function enqueue_custom_script_for_tutor() {
    if (is_singular('lesson')) { // التحقق من أن الصفحة الحالية هي درس
        global $post;
        $lesson_id = $post->ID;
        $course_id = $post->post_parent;

        $inline_script = "
        var tutorLessonData = {
            lesson_id: {$lesson_id},
            course_id: {$course_id}
        };
    ";
    wp_add_inline_script('jquery', $inline_script);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_script_for_tutor');



add_filter('tutor_lesson_details_response', function( $data, $lesson_id ) {
    $duration = get_post_meta( $lesson_id, '_lesson_duration', true );

    if ( ! empty( $duration ) ) {
        $data['_lesson_duration'] = $duration;
    }

    return $data;
}, 10, 2);

add_action( 'save_post_lesson', function( $post_id, $post, $update ) {
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['_lesson_duration'] ) ) {
        $duration = sanitize_text_field( $_POST['_lesson_duration'] );
        update_post_meta( $post_id, '_lesson_duration', $duration );
    }
}, 10, 3);


add_filter( 'tutor_course_details_response', 'tlcf_display_custom_field_data' );
function tlcf_display_custom_field_data( array $data ) {
	$course_id   = $data['ID'];
	$telegram_url = get_post_meta( $course_id, '_telegram_url', true );
	if ( $telegram_url ) {
		$data['_telegram_url'] = $telegram_url;
	}
	return $data;
}
add_action( 'save_post_courses', 'tlcf_save_course_meta' );
function tlcf_save_course_meta( int $post_id ) {
	$telegram_url = sanitize_text_field( wp_unslash( $_POST['_telegram_url'] ) ?? '' );
	if ( $telegram_url ) {
		update_post_meta( $post_id, '_telegram_url', $telegram_url );
	}
}


// ======================
//  حقوق ودعم فني احترافي
// ======================
add_action('admin_footer', function() {
    if (!current_user_can('manage_options')) return;
    echo '<div style="margin:32px 0 0 0;padding:16px 0 0 0;text-align:center;font-size:15px;color:#444;opacity:0.85;">
    <span style="font-weight:bold;">جميع الحقوق محفوظة &copy; ' . date('Y') . ' المركز الوطني للتعليم الإلكتروني</span><br>
    <span>للدعم الفني عبر الواتساب: '
        .'<a href="https://wa.me/966555555555" target="_blank" style="color:#25d366;font-weight:bold;text-decoration:none;">+966555555555</a>'
        .' &nbsp;|&nbsp; '
        .'<a href="https://wa.me/966512345678" target="_blank" style="color:#25d366;font-weight:bold;text-decoration:none;">+966512345678</a>'
    .'</span>
    </div>';
});
