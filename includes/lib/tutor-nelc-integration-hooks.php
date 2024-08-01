<?php

function course_integrate_status($course_id) {
    $course_itegrate = get_option('lmtni_xapi_courses_integrate');
    $link_status = get_post_meta($course_id, 'tutor_nelc_integration_link_course', true) === 'on' ? 'checked' : '';

    if ($link_status != 'checked' && !$course_itegrate) {
        return false;
    }

    return true;
}

add_action( 'add_meta_boxes', 'tutor_nelc_integration_link_courses_box' );
function tutor_nelc_integration_link_courses_box() {
    add_meta_box(
        'tutor_nelc_integration_link_courses',
        __( 'NELC Integration', 'lamoud-nelc-xapi' ),
        'tutor_nelc_integration_link_courses_html',
        'courses',
        'normal',
        'high'
    );
}

function tutor_nelc_integration_link_courses_html( $post ) {

    $course_itegrate = get_option('lmtni_xapi_courses_integrate');

    add_post_meta( $post->ID, 'tutor_nelc_integration_link_course', 'on', true );
    $link_status =  get_post_meta( $post->ID, 'tutor_nelc_integration_link_course', true ) === 'on' ? 'checked' : '';
    ?>
    <div class="tutor-row tutor-mb-32">
        <div class="tutor-col-12 tutor-col-md-5">
            <label class="tutor-course-setting-label"><?php echo __( 'NELC Integration', 'lamoud-nelc-xapi' ); ?></label>
        </div>
        <div class="tutor-col-12 tutor-col-md-7">

            <?php if( ! $course_itegrate ) : ?>
                <label class="tutor-form-toggle">
                    <input id="course_setting_toggle_switch__tutor_is_public_course" type="checkbox" class="tutor-form-toggle-input" name="tutor_nelc_integration_link_courses" value="<?php echo $link_status; ?>" <?php echo $link_status; ?>>
                        <span class="tutor-form-toggle-control"></span>
                </label>
            <?php else: ?>
                <p>تم تفعيل الربط التلقائي لجميع الدورات</p>
            <?php endif; ?>

            <div class="tutor-fs-7 tutor-has-icon tutor-color-muted tutor-d-flex tutor-mt-12">
                <i class="tutor-icon-circle-info-o tutor-mt-4 tutor-mr-8"></i>
                <?php echo __( 'Upon activation, the courses will linked with NELC.', 'lamoud-nelc-xapi' ); ?>
            </div>
        </div>
    </div>
    <?php




}

add_action( 'save_post', 'save_tutor_nelc_integration_link_courses', 10,3 );
function save_tutor_nelc_integration_link_courses(  $post_id, $post, $update ) {   

    if ( 'courses' !== $post->post_type ) {
        return;
    }

    if ( isset( $_POST['tutor_nelc_integration_link_courses'] ) ) {
        $status = esc_url_raw( $_POST['tutor_nelc_integration_link_courses'] );
        update_post_meta( $post_id, 'tutor_nelc_integration_link_course', 'on' );
    }else{
        update_post_meta( $post_id, 'tutor_nelc_integration_link_course', 'of' );
    }

}


// add_action('wp_ajax_ajax_check_if_profile_complete', 'ajax_check_if_profile_complete');
// add_action('wp_ajax_nopriv_ajax_check_if_profile_complete', 'ajax_check_if_profile_complete');
// function ajax_check_if_profile_complete(){
// 	global $current_user;


// 	$user_name = $current_user->first_name .' '. $current_user->last_name;

//     if ( $current_user ) {

//         $permission = get_user_meta( $current_user->ID, 'nelc_national_id' , true );
                
//         if ( empty( $permission ) || empty( $user_name ) || $user_name == ' ' ) {
//             wp_send_json_error( get_option('lmtni_xapi_complete_profile') );
//         }


//     }else {
//         wp_send_json_error( get_option('lmtni_xapi_complete_profile') );
//     }

//     wp_send_json_success();
// }

// add_action('tutor_before_enroll', 'check_if_userprofile_complete');
// function check_if_userprofile_complete(){
// 	global $current_user;


// 	$user_name = $current_user->first_name .' '. $current_user->last_name;

//     if ( $current_user ) {

//         $permission = get_user_meta( $current_user->ID, 'nelc_national_id' , true );
                
//         if ( empty( $permission ) || empty( $user_name ) || $user_name == ' ' ) {
//             wp_send_json_error( get_option('lmtni_xapi_complete_profile') );
//         }


//     }else {
//         wp_send_json_error();
//     }

// }

add_action('tutor_after_enroll', 'nelec_register_statemente_tutor');
function nelec_register_statemente_tutor ( $course_id )
{
    if (!course_integrate_status($course_id)) {
        return;
    }
    global $post;
    $user = wp_get_current_user();

    // Get student info
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );

    // Get author info
    $author_id = $post->post_author;
	$author = get_userdata($author_id);

    // Get course info
    $course = get_post( $course_id );

    $body = tutor_nelc_integration()->register_statment( 'registered', [
        'name' => "$user->display_name",
        'email' => "$user->user_email",
        'courseId' => "$course->ID",
        'courseName' => "$course->post_title",
        'courseDesc' =>  strip_tags($course->post_content),    
        'instructor' => "$author->display_name",
        'inst_email' => "$author->user_email",
    ]);
    
    $response = tutor_nelc_integration()->register_interactions( $body );
    if (is_wp_error($response)) {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }else{
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['body']);
    }
    
}

add_action('tutor/lesson_list/before/topic', 'nelec_initialize_statemente_tutor');
//add_action('tutor/course/started', 'nelec_initialize_statemente_tutor');
function nelec_initialize_statemente_tutor ( $course_id ){
    if (!course_integrate_status($course_id)) {
        return;
    }
    global $post;
    $user = wp_get_current_user();

    // Get student info
    $ntd = get_user_meta( $user->ID, 'nelc_national_id' , true );

    // Get author info
    $author_id = $post->post_author;
	$author = get_userdata($author_id);

    // Get course info
    $course = get_post( $course_id );

    $is_init = get_user_meta( get_current_user_id(), "course_init_$course_id", true );

    if($is_init && $is_init == 'yes'){
       // update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }else{
        $body = tutor_nelc_integration()->register_statment( 'initialized', [
            'name' => "$user->display_name",
            'email' => "$user->user_email",
            'courseId' => "$course->ID",
            'courseName' => "$course->post_title",
            'courseDesc' => substr( strip_tags($course->post_content), 0, 50 ) ?? $course->post_title,    
            'instructor' => "$author->display_name",
            'inst_email' => "$author->user_email",
        ]);
        
        $response = tutor_nelc_integration()->register_interactions( $body );
        if (is_wp_error($response)) {
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
        }else{
            update_user_meta( get_current_user_id(), "course_init_$course_id", 'yes' );
            update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['body']);
        }

    }

}

add_action('tutor_lesson_completed_after', 'lesson_completed_hook');
function lesson_completed_hook($lesson_id) {

    global $post;

    $user = wp_get_current_user();
    $lesson = get_post( $lesson_id );
    $course_id = tutor_utils()->get_course_id_by_lesson($lesson_id);
    $course = get_post( $course_id );

    if (!course_integrate_status($course_id)) {
        return;
    }

    $author_id = $course->post_author;
    $author = get_userdata($author_id);

    $percentage = tutor_utils()->get_course_completed_percent( $course_id, $user->ID );
    $scaled = $percentage / 100;

    $body1 = tutor_nelc_integration()->register_statment( 'completed', [
        'name' => "$user->display_name",
        'email' => "$user->user_email",
        'lessonUrl'=> "$lesson->guid",
        'lessonName'=> "$lesson->post_title",
        'lessonDesc'=> strip_tags($lesson->post_content),
        'instructor' => "$author->display_name",
        'inst_email' => "$author->user_email",
        'courseId' => "$course->ID",
        'courseName' => "$course->post_title",
        'courseDesc' => substr( strip_tags($course->post_content), 0, 50 ) ?? $course->post_title,
    ]);
    $response1 = tutor_nelc_integration()->register_interactions( $body1 );

    $body = tutor_nelc_integration()->register_statment( 'progressed', [
        'name' => "$user->display_name",
        'email' => "$user->user_email",
        'courseId' => "$course->ID",
        'courseName' => "$course->post_title",
        'courseDesc' => substr( strip_tags($course->post_content), 0, 50 ) ?? $course->post_title,
        'instructor' => "$author->display_name",
        'inst_email' => "$author->user_email",
        'scaled' => round($scaled, 2),
        'completion' => $percentage == 100 ? true : false,
    ]);

    $response = tutor_nelc_integration()->register_interactions( $body );

    if (is_wp_error($response1)) {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }else{
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response1['body']);
    }

    if (is_wp_error($response)) {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }else{
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['body']);
    }

}

add_action('tutor_quiz/attempt_ended', 'quiz_attempt_hook');
function quiz_attempt_hook($attempt_id) {
    $attempt_data = tutor_utils()->get_attempt($attempt_id);
    
    $user =  wp_get_current_user();

    $quiz_id = tutor_utils()->avalue_dot('quiz_id', $attempt_data);
    $quiz_data = get_post($quiz_id);

    $course_id = tutor_utils()->avalue_dot('course_id', $attempt_data);
    $course = get_post($course_id);
    if (!course_integrate_status($course_id)) {
        return;
    }
    $author_id = $course->post_author;
    $author = get_userdata($author_id);

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

    $body = tutor_nelc_integration()->register_statment('attempted', [
        'name' => $user->display_name,
        'email' => $user->user_email,
        'quizUrl' => get_permalink($quiz_id),
        'quizName' => $quiz_data->post_title,
        'quizDesc' => strip_tags($quiz_data->post_content),
        'instructor' => $author->display_name,
        'inst_email' => $author->user_email,
        'attempNumber' => $attempt_count,
        'courseId' => $course->ID,
        'courseName' => $course->post_title,
        'courseDesc' => substr(strip_tags($course->post_content), 0, 50) ?? $course->post_title,
        'scaled' => round($percentage / 100, 2),
        'raw' => $points,
        'min' => $min,
        'max' => $total_points,
        'completion' => true,
        'success' => $is_passed == 1 ? true : false,
    ]);

    $response = tutor_nelc_integration()->register_interactions($body);

    // echo "<pre>";
    // print_r($response);
    // echo "</pre>";
    // echo "================================================================";
    // echo "================================================================";
    // echo "<pre>";
    // print_r($body);
    // echo "</pre>";
    // echo "<pre>";
    // print_r($attempt_data);
    // echo "</pre>";
    // echo "=========================";
    // echo "<pre>";
    // print_r([
    //     'name' => $user->display_name,
    //     'email' => $user->user_email,
    //     'quizUrl' => get_permalink($quiz_id),
    //     'quizName' => $quiz_data->post_title,
    //     'quizDesc' => strip_tags($quiz_data->post_content),
    //     'instructor' => $author->display_name,
    //     'inst_email' => $author->user_email,
    //     'attempNumber' => $attempt_count,
    //     'courseId' => $course->ID,
    //     'courseName' => $course->post_title,
    //     'courseDesc' => substr(strip_tags($course->post_content), 0, 50) ?? $course->post_title,
    //     'scaled' => round($percentage / 100, 2),
    //     'raw' => $points,
    //     'min' => $min,
    //     'max' => $total_points,
    //     'completion' => true,
    //     'success' => $is_passed == 1 ? true : false,
    // ]);
    // echo "</pre>";

    // exit;

    if (get_option('lmtni_xapi_notific', true) !== 'on') {
        return;
    }

    if (is_wp_error($response)) {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }else{
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['body']);
    }

    
}

add_action('tutor_course_complete_after', 'course_completed_hook', 10, 2);
function course_completed_hook($course_id) {
    if (!course_integrate_status($course_id)) {
        return;
    }
    $user = wp_get_current_user();
    $course = get_post($course_id);
    $author = get_userdata($course->post_author);

    // التحقق مما إذا كانت الدورة مكتملة
    $is_comp = tutor_utils()->is_completed_course($course_id);

    // الحصول على رابط الشهادة
    $certificate_link = $is_comp ? esc_url(site_url("/?cert_hash=" . $is_comp->completed_hash)) : null;

    // إنشاء بيانات التصريح الأول
    $body = tutor_nelc_integration()->register_statment('completedCourse', [
        'name' => $user->display_name,
        'email' => $user->user_email,
        'courseId' => $course->ID,
        'courseName' => $course->post_title,
        'courseDesc' => substr(strip_tags($course->post_content), 0, 50) ?? $course->post_title,
        'instructor' => $author->display_name,
        'inst_email' => $author->user_email,
    ]);

    // إرسال بيانات التصريح الأول
    $response = tutor_nelc_integration()->register_interactions($body);

    // إنشاء بيانات التصريح الثاني
    $body1 = tutor_nelc_integration()->register_statment('earned', [
        'name' => $user->display_name,
        'email' => $user->user_email,
        'certUrl' => $certificate_link,
        'certName' => $certificate_link,
        'courseId' => $course->ID,
        'courseName' => $course->post_title,
        'courseDesc' => substr(strip_tags($course->post_content), 0, 50) ?? $course->post_title,
    ]);

    // إرسال بيانات التصريح الثاني
    $response1 = tutor_nelc_integration()->register_interactions($body1);

    // التحقق من وجود أخطاء في الاستجابات وتحديث البيانات الشخصية بناءً على ذلك
    if (get_option('lmtni_xapi_notific', true) !== 'on') {
        return;
    }
    if (is_wp_error($response)) {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    } else {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['body']);
    }

    if (is_wp_error($response1)) {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    } else {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response1['body']);
    }

    // echo "<pre>";
    //     print_r($response);
    // echo "</pre>";
    // echo "<pre>";
    //     print_r($response1);
    // echo "</pre>";
    // exit;


}

add_action('tutor_after_rating_placed', 'course_rated_hook');
function course_rated_hook( $comment_id )
{
    $course_itegrate = get_option('lmtni_xapi_courses_integrate');
    $link_status =  get_post_meta( get_the_ID(), 'tutor_nelc_integration_link_course', true ) === 'on' ? 'checked' : '';
    if ($link_status != 'checked' &&  !$course_itegrate) {
        # code...
    }
    $user = wp_get_current_user();
    $comment = get_post( $comment_id );
    $course_id = $comment->post_parent;
    $course = get_post( $course_id );
    if (!course_integrate_status($course_id)) {
        return;
    }
    $author  = get_userdata($course->post_author);


    $rate_info = tutor_utils()->get_course_rating_by_user($user->ID);
    $rate_star = $rate_info->rating;
    $rate_comment = $rate_info->review;

    $body = tutor_nelc_integration()->register_statment( 'rated', [
        'name' => "$user->display_name",
        'email' => "$user->user_email",
        'courseId' => "$course->ID",
        'courseName' => "$course->post_title",
        'courseDesc' => substr( strip_tags($course->post_content), 0, 50 ) ?? $course->post_title,    
        'instructor' => "$author->display_name",
        'inst_email' => "$author->user_email",
        'scaled' => $rate_star / 5,
        'raw' => $rate_star,
        'min' => 0,
        'max' => 5,
        'comment' => "$rate_comment",
    ]);
    

    $response = tutor_nelc_integration()->register_interactions( $body );

    if (get_option('lmtni_xapi_notific', true) !== 'on') {
        return;
    }
    if (is_wp_error($response)) {
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', 'error');
    }else{
        update_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', $response['body']);
    }

    // echo "<pre>";
    //     print_r($response);
    // echo "</pre>";
    // exit;
}
