
<?php

use tutorLmsLrsPlugin\includes\Interactions\XapiIntegration;

$test = '';

if( isset( $_POST['lmtni_xapi_select_statement'] ) ){
    
    $test = $_POST['lmtni_xapi_select_statement'];

    $xapiSender = new XapiIntegration;
    $response = null;

    switch ($test) {
        case 'register':
            $response = $xapiSender->Registered([
                'name' => 'Mahmoud Hassan',
                'email' => 'betalamoud@gmail.com',
                'duration' => 'PT30H00M00S',
                'learneMobileNo' => '+201000944804',
                'learnerFullName' => 'Mahmoud Hassan Ali Attya',
                'learnerNationality' => 'Egypt',
                'dateOfBirth' => '20/09/1988',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);
            break;
        case 'initialized':
            $response = $xapiSender->Initialized([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);
        break;
        case 'watched':
            $response = $xapiSender->Watched([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'lessonUrl'=> get_site_url() . '/course/video/view.php?id=123',
                'lessonName'=> 'Test video',
                'lessonDesc'=> 'Video Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'completion' => true,
                'duration' => 'PT00H05M00S',
            ]);
        break;
        case 'completed_lesson':
            $response = $xapiSender->Completed([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'lessonUrl'=> get_site_url() . '/course/lesson/view.php?id=123',
                'lessonName'=> 'Test lesson',
                'lessonDesc'=> 'Lesson Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'lessonDuration' => 'PT30H00M00S',
            ]);
        break;
        case 'completed_unit':
            $response = $xapiSender->CompletedUnit([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'unitUrl'=> get_site_url() . '/course/section/view.php?id=123',
                'unitName'=> 'Test unit',
                'unitDesc'=> 'Unit Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);        break;
        case 'progressed':
            $response = $xapiSender->Progressed([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'scaled' => 1,
                'completion' => true,
                'courseLang' => 'en-US',
            ]);
        break;
        case 'attempted':
            $response = $xapiSender->Attempted([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'quizUrl' => get_site_url() . '/course/quiz/view.php?id=123',
                'quizName' => 'Test quiz',
                'quizDesc' => 'quiz Desc',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'attempNumber' => '1',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'scaled' => 0.8,
                'raw' => 8,
                'min' => 5,
                'max' => 10,
                'completion' => true,
                'success' => true,
            ]);
        break;
        case 'completed_course':
            $response = $xapiSender->CompletedCourse([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
            ]);
            break;
        case 'earned':
            $response = $xapiSender->Earned([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'certUrl' => get_site_url() . '/course/cert/view.php?id=123',
                'certName' => 'Test certificate',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
            ]);
        break;
        case 'rated':
            $response = $xapiSender->Rated([
                'name' => 'Mahmoud Hassan',
                'email' => 'betatutor@gmail.com',
                'courseId' => 123,
                'courseName' => 'Test course',
                'courseDesc' => 'course Desc',
                'courseLang' => 'en-US',
                'instructor' => 'Mr Hassan',
                'inst_email' => 'mrhassan@test.com',
                'scaled' => 0.8,
                'raw' => 4,
                'min' => 0,
                'max' => 5,
                'comment' => 'good course',
            ]);
        break;
        
        default:
        $response = "Invalid statement type selected.";
        break;
    }

        if (!empty($response)) {
            if (isset($response['http_code'])) {

                    $html .= '<h2 style="width: max-content; margin: 0 auto; margin-bottom: 30px; font-size: 40px;"><span style="color: #5bc0de;">' . htmlspecialchars($test) . '</span></h2>';
                    $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;margin-bottom:8px">';
                    $html .= '<code style="color: #d9534f;"><strong>HTTP Code:</strong>' . esc_html($response['http_code']) . '</code>';
                    $html .= '</pre>';

                    $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;margin-bottom:8px">';
                    $html .= '<code style="color: #d9534f;"><strong>Response:</strong>' .  esc_html($response['response']) . '</code>';
                    $html .= '</pre>';

                
                if (!empty($response['error'])) {
                    // $html .= '<h2 style="direction: ltr;">Error</h2>';
                    $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;margin-bottom:8px">';
                    $html .= '<code style="color: #d9534f;"><strong>Error:</strong>' . esc_html($response['error']) . '</code>';
                    $html .= '</pre>';
                } else {
                    if ($response['http_code'] == 200) {
                        $formatted_data = json_encode(json_decode($response['data_sent']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                        // $html .= '<h2 style="direction: ltr;">Body</h2>';    
                        $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;margin-bottom:8px">';
                        $html .= '<code style="color: #5bc0de;"> <strong>Body: </strong>' . esc_html($formatted_data) . '</code>';
                        $html .= '</pre>';
                    }
                }
            } else {
                $html .= '<div class="notice notice-info"><p>' . esc_html($response) . '</p></div>';
            }
        } else {
            $html .= '<div class="notice notice-info"><p>No data</p></div>';
        }


        // if (is_wp_error($response)) {
        //     // $response is a WP_Error
        //     $error_message = $response->get_error_message();
        //     // Handle the error as needed
        //     $html .= '<h2 style="direction: ltr;">Error</h2>';
        //     $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
        //     $html .= '<code style="color: #d9534f;">' . htmlspecialchars($error_message) . '</code>';
        //     $html .= '</pre>';
        // } else {
        //     $html .= '<h2 style="direction: ltr;">' . htmlspecialchars($test) . '</h2>';
        //     $html .= '<h2 style="direction: ltr;">Response</h2>';
        //     $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
        //     $html .= '<code style="color: #5bc0de;">' . (!is_wp_error($response['response']) && json_encode($response['response']) ? htmlspecialchars(json_encode($response['response'])) : 'خطأ غير متوقع، برجاء التأكد من بيانات الإتصال') . '</code>';
        //     $html .= '</pre>';
        
        //     $html .= '<h2 style="direction: ltr;">Body</h2>';
        //     $html .= '<pre style="background: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
        //     $html .= '<code>' . (!is_wp_error($response['body']) ? htmlspecialchars($response['body']) : 'خطأ غير متوقع، برجاء التأكد من بيانات الإتصال') . '</code>';
        //     $html .= '</pre>';
        
        //     $html .= '<h2 style="direction: ltr;">Statement</h2>';
        //     $html .= '<pre style="background: #333; color: #fff; direction: ltr; padding: 16px; border: 1px solid #ccc; border-radius: 4px;">';
        //     $html .= '<code>' . htmlspecialchars(json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</code>';
        //     $html .= '</pre>';
        // }
    

}


