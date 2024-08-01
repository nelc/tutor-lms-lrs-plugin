
<?php
$test = '';

if( isset( $_POST['lmtni_xapi_select_statement'] ) ){
    $test = $_POST['lmtni_xapi_select_statement'];

    $response = null;
    switch ($test) {
        case 'register':
            $body = tutor_nelc_integration()->register_statment( 'registered', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
            break;
        case 'initialized':
            $body = tutor_nelc_integration()->register_statment( 'initialized', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'watched':
            $body = tutor_nelc_integration()->register_statment( 'watched', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'lessonUrl'=> '/courseID/unitId/lessonId',
                'lessonName'=> 'Test lesson',
                'lessonDesc'=> 'Lesson Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'completion' => true,
                'duration' => 'PT15M',
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'completed_lesson':
            $body = tutor_nelc_integration()->register_statment( 'completed', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'lessonUrl'=> '/courseID/unitId/lessonId',
                'lessonName'=> 'Test lesson',
                'lessonDesc'=> 'Lesson Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'completed_unit':
            $body = tutor_nelc_integration()->register_statment( 'completedUnit', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'unitUrl'=> '/courseID/unitId',
                'unitName'=> 'Test unit',
                'unitDesc'=> 'Unit Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'progressed':
            $body = tutor_nelc_integration()->register_statment( 'progressed', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'scaled' => '1',
                'completion' => true,
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'attempted':
            $body = tutor_nelc_integration()->register_statment( 'attempted', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'quizUrl' => '/unitId/quizId',
                'quizName' => 'Test quiz',
                'quizDesc' => 'quiz Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'attempNumber' => '1',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'scaled' => '1',
                'raw' => '50',
                'min' => '25',
                'max' => '50',
                'completion' => true,
                'success' => true,
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'completed_course':
            $body = tutor_nelc_integration()->register_statment( 'completedCourse', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'earned':
            $body = tutor_nelc_integration()->register_statment( 'earned', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'certUrl' => '/path/to/certificate',
                'certName' => 'Test certificate',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc'
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        case 'rated':
            $body = tutor_nelc_integration()->register_statment( 'rated', [
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => '123',
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'scaled' => 0.8,
                'raw' => 4,
                'min' => 0,
                'max' => 5,
                'comment' => 'good course',
            ]);
            
            $response = tutor_nelc_integration()->register_interactions( $body );
        break;
        
        default:
            $response = new WP_Error();
            $response->add('custom_error', 'Please select a valid statement');
        break;
    }

        //print_r($response);

        if (is_wp_error($response)) {
            // $response is a WP_Error
            $error_message = $response->get_error_message();
            // Handle the error as needed
            $html .= '<h2 style="direction: ltr;">Error</h2>';
            $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
            $html .= '<code style="color: #d9534f;">' . htmlspecialchars($error_message) . '</code>';
            $html .= '</pre>';
        } else {
            $html .= '<h2 style="direction: ltr;">' . htmlspecialchars($test) . '</h2>';
            $html .= '<h2 style="direction: ltr;">Response</h2>';
            $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
            $html .= '<code style="color: #5bc0de;">' . (!is_wp_error($response['response']) && json_encode($response['response']) ? htmlspecialchars(json_encode($response['response'])) : 'خطأ غير متوقع، برجاء التأكد من بيانات الإتصال') . '</code>';
            $html .= '</pre>';
        
            $html .= '<h2 style="direction: ltr;">Body</h2>';
            $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
            $html .= '<code>' . (!is_wp_error($response['body']) ? htmlspecialchars($response['body']) : 'خطأ غير متوقع، برجاء التأكد من بيانات الإتصال') . '</code>';
            $html .= '</pre>';
        
            $html .= '<h2 style="direction: ltr;">Statement</h2>';
            $html .= '<pre style="background: #333; color: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
            $html .= '<code>' . htmlspecialchars(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</code>';
            $html .= '</pre>';
        }
        


    

}


