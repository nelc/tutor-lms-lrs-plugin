<?php
// Don't allow direct access
if (!defined('ABSPATH')) {
    exit;
}

class NELC_Integration_Settings {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes'], 10, 3);
        
        // إضافة دوال لحقل مدة الدرس
        add_action('tutor_lesson_edit_modal_form_after', [$this, 'add_lesson_duration_field_to_modal'], 10, 1);
        add_filter('tutor_lesson_modal_post_data', [$this, 'add_lesson_duration_to_post_data']);
        add_action('tutor/modal_lesson_update/after', [$this, 'save_lesson_duration_on_modal_update'], 10, 2);
        add_action('tutor_lesson_created', [$this, 'save_lesson_duration_on_create'], 10, 1);
        add_action('save_post_lesson', [$this, 'save_lesson_duration_on_create'], 10, 1);
        add_action('tutor_lesson_updated', [$this, 'save_lesson_duration_on_update'], 10, 1);
        add_action('admin_footer', [$this, 'add_lesson_duration_js']);
        add_action('wp_ajax_save_lesson_duration', [$this, 'save_lesson_duration_ajax']);
        add_action('admin_init', [$this, 'register_lesson_duration_ajax']);
        //add_action('tutor_lesson/single/before/content', [$this, 'display_lesson_duration_in_popup']);
        //add_filter('tutor_course/single/lesson/title', [$this, 'add_duration_to_course_curriculum'], 10, 2);
        //add_action('wp_head', [$this, 'add_lesson_duration_styles']);
        add_action('admin_head', [$this, 'add_lesson_duration_styles']);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'nelc_integration_settings',
            __('NELC Integration Settings', 'tutor-nelc-xapi'),
            [$this, 'render_meta_boxes'],
            'courses',
            'normal',
            'high'
        );
    }

    public function render_meta_boxes($post) {
        wp_nonce_field('nelc_course_settings_nonce', 'nelc_course_settings_nonce');
        
        $course_integrate = get_option('lmtni_xapi_courses_integrate');
        $link_status = get_post_meta($post->ID, 'tutor_nelc_integration_link_course', true) === 'on' ? 'checked' : '';
        
        $duration = get_post_meta($post->ID, '_nelc_course_duration', true) ?: 'PT30H00M00S';
        $language = get_post_meta($post->ID, '_nelc_course_language', true) ?: 'en-US';
        
        // تحويل صيغة المدة ISO 8601 إلى ساعات ودقائق لعرضها بشكل أفضل
        $hours = 30;
        $minutes = 0;
        if (preg_match('/PT(\d+)H(\d+)M(\d+)S/', $duration, $matches)) {
            $hours = intval($matches[1]);
            $minutes = intval($matches[2]);
        }
        
        // حساب المدة الإجمالية للدورة من الدروس
        $total_lesson_duration = $this->calculate_course_total_duration($post->ID);
        $lesson_hours = floor($total_lesson_duration / 60);
        $lesson_minutes = $total_lesson_duration % 60;
        ?>
        <div class="tutor-row tutor-mb-32">
            <div class="tutor-col-12 tutor-col-md-5">
                <label class="tutor-course-setting-label"><?php echo __('NELC Integration', 'lamoud-nelc-xapi'); ?></label>
            </div>
            <div class="tutor-col-12 tutor-col-md-7">
                <?php if (!$course_integrate) : ?>
                    <label class="tutor-form-toggle">
                        <input type="checkbox" name="tutor_nelc_integration_link_courses" value="on" <?php echo $link_status; ?>>
                        <span class="tutor-form-toggle-control"></span>
                    </label>
                <?php else: ?>
                    <p>تم تفعيل الربط التلقائي لجميع الدورات</p>
                <?php endif; ?>
                <div class="tutor-fs-7 tutor-has-icon tutor-color-muted tutor-d-flex tutor-mt-12">
                    <i class="tutor-icon-circle-info-o tutor-mt-4 tutor-mr-8"></i>
                    <?php echo __('Upon activation, the courses will be linked with NELC.', 'lamoud-nelc-xapi'); ?>
                </div>
            </div>
        </div>
        
        <div class="nelc-meta-box-container">
            <p>
                <label for="nelc_course_duration_hours">
                    <strong><?php _e('Course Duration', 'tutor-nelc-xapi'); ?></strong>
                </label>
                <div class="duration-inputs">
                    <input type="number" id="nelc_course_duration_hours" name="nelc_course_duration_hours" value="<?php echo esc_attr($hours); ?>" min="0" class="small-text"> 
                    <label for="nelc_course_duration_hours"><?php _e('Hours', 'tutor-nelc-xapi'); ?></label>
                    
                    <input type="number" id="nelc_course_duration_minutes" name="nelc_course_duration_minutes" value="<?php echo esc_attr($minutes); ?>" min="0" max="59" class="small-text"> 
                    <label for="nelc_course_duration_minutes"><?php _e('Minutes', 'tutor-nelc-xapi'); ?></label>
                    
                    <input type="hidden" id="nelc_course_duration" name="nelc_course_duration" value="<?php echo esc_attr($duration); ?>">
                </div>
                
                <div style="margin-top: 10px;">
                    <input type="checkbox" id="use_lesson_duration" name="use_lesson_duration" value="1">
                    <label for="use_lesson_duration">
                        <?php _e('Use total lesson duration', 'tutor-nelc-xapi'); ?> 
                        (<?php echo sprintf(__('%d hours, %d minutes', 'tutor-nelc-xapi'), $lesson_hours, $lesson_minutes); ?>)
                    </label>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        // تحديث قيمة المدة في الصيغة المطلوبة عند تغيير الساعات أو الدقائق
                        $('#nelc_course_duration_hours, #nelc_course_duration_minutes').on('change', function() {
                            var hours = $('#nelc_course_duration_hours').val() || 0;
                            var minutes = $('#nelc_course_duration_minutes').val() || 0;
                            var duration = 'PT' + hours + 'H' + minutes + 'M00S';
                            $('#nelc_course_duration').val(duration);
                        });
                        
                        // استخدام المدة الإجمالية للدروس
                        $('#use_lesson_duration').on('change', function() {
                            if ($(this).is(':checked')) {
                                $('#nelc_course_duration_hours').val(<?php echo $lesson_hours; ?>);
                                $('#nelc_course_duration_minutes').val(<?php echo $lesson_minutes; ?>);
                                var duration = 'PT' + <?php echo $lesson_hours; ?> + 'H' + <?php echo $lesson_minutes; ?> + 'M00S';
                                $('#nelc_course_duration').val(duration);
                            }
                        });
                    });
                </script>
            </p>
            <p>
                <label for="nelc_course_language">
                    <strong><?php _e('Course Language', 'tutor-nelc-xapi'); ?></strong>
                </label>
                <select id="nelc_course_language" name="nelc_course_language" class="widefat">
                    <option value="en-US" <?php selected($language, 'en-US'); ?>><?php _e('English (US)', 'tutor-nelc-xapi'); ?></option>
                    <option value="ar-SA" <?php selected($language, 'ar-SA'); ?>><?php _e('Arabic (Saudi Arabia)', 'tutor-nelc-xapi'); ?></option>
                    <option value="fr-FR" <?php selected($language, 'fr-FR'); ?>><?php _e('French', 'tutor-nelc-xapi'); ?></option>
                    <option value="de-DE" <?php selected($language, 'de-DE'); ?>><?php _e('German', 'tutor-nelc-xapi'); ?></option>
                    <option value="es-ES" <?php selected($language, 'es-ES'); ?>><?php _e('Spanish', 'tutor-nelc-xapi'); ?></option>
                </select>
            </p>
        </div>
        <style>
            .duration-inputs label {
                margin-right: 15px;
            }
            .duration-inputs input {
                width: 70px;
            }
        </style>
        <?php
    }

    public function save_meta_boxes($post_id, $post, $update) {
        if (!isset($_POST['nelc_course_settings_nonce']) || !wp_verify_nonce($_POST['nelc_course_settings_nonce'], 'nelc_course_settings_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $status = isset($_POST['tutor_nelc_integration_link_courses']) ? 'on' : 'off';
        update_post_meta($post_id, 'tutor_nelc_integration_link_course', $status);
        
        if (isset($_POST['nelc_course_duration'])) {
            update_post_meta($post_id, '_nelc_course_duration', sanitize_text_field($_POST['nelc_course_duration']));
        } else if (isset($_POST['nelc_course_duration_hours']) && isset($_POST['nelc_course_duration_minutes'])) {
            $hours = intval(sanitize_text_field($_POST['nelc_course_duration_hours']));
            $minutes = intval(sanitize_text_field($_POST['nelc_course_duration_minutes']));
            $duration = 'PT' . $hours . 'H' . $minutes . 'M00S';
            update_post_meta($post_id, '_nelc_course_duration', $duration);
        }
        
        if (isset($_POST['nelc_course_language'])) {
            update_post_meta($post_id, '_nelc_course_language', sanitize_text_field($_POST['nelc_course_language']));
        }
    }
    
    /* بداية الدوال الخاصة بمدة الدرس */
    
    /**
     * إضافة حقل المدة في واجهة تحرير الدرس ضمن النافذة المنبثقة
     */
    public function add_lesson_duration_field_to_modal($post) {
        // الحصول على قيمة المدة المخزنة مسبقًا (إن وجدت)
        $lesson_id = (is_object($post) && isset($post->ID)) ? $post->ID : (isset($_GET['lesson_id']) ? $_GET['lesson_id'] : 0);
        $duration = get_post_meta($lesson_id, '_lesson_duration', true);
        ?>
        <div class="tutor-option-field-row">
            <div class="tutor-option-field-label">
                <label for="lesson_duration"><?php _e('مدة الدرس (بالدقائق)', 'tutor'); ?></label>
            </div>
            <div class="tutor-option-field">
                <input type="number" id="lesson_duration" name="lesson_duration" value="<?php echo esc_attr($duration); ?>" min="1">
                <p class="description"><?php _e('أدخل مدة الدرس بالدقائق', 'tutor'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * إضافة قيمة مدة الدرس إلى البيانات المرسلة عند حفظ الدرس
     */
    public function add_lesson_duration_to_post_data($post_data) {
        if (isset($_POST['lesson_duration'])) {
            $post_data['_lesson_duration'] = sanitize_text_field($_POST['lesson_duration']);
        }
        return $post_data;
    }

    /**
     * حفظ قيمة مدة الدرس باستخدام AJAX
     */
    public function save_lesson_duration_ajax() {
        // التحقق من الأمان
        $nonce = isset($_POST['_tutor_nonce']) ? $_POST['_tutor_nonce'] : '';
        if (!wp_verify_nonce($nonce, 'tutor_nonce_action')) {
            wp_send_json_error(['message' => __('Invalid nonce', 'tutor')]);
            return;
        }

        // التحقق من وجود معرف الدرس
        if (isset($_POST['lesson_id']) && isset($_POST['lesson_duration'])) {
            $lesson_id = absint($_POST['lesson_id']);
            $duration = sanitize_text_field($_POST['lesson_duration']);
            
            // تحديث أو إضافة قيمة المدة
            update_post_meta($lesson_id, '_lesson_duration', $duration);
            
            // تحديث المدة الإجمالية للدورة
            $course_id = get_post_meta($lesson_id, '_tutor_course_id_for_lesson', true);
            if ($course_id) {
                $this->calculate_course_total_duration($course_id);
            }
            
            wp_send_json_success(['message' => __('تم حفظ مدة الدرس بنجاح', 'tutor')]);
        } else {
            wp_send_json_error(['message' => __('بيانات مفقودة', 'tutor')]);
        }
    }

    /**
     * إضافة JavaScript لحفظ مدة الدرس عند تقديم النموذج
     */
    public function add_lesson_duration_js() {
        if (function_exists('is_admin') && is_admin()) {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // حفظ مدة الدرس عند إرسال النموذج
                $(document).on('submit', '.tutor-modal-form-wrap form', function() {
                    const lessonId = $(this).find('input[name="lesson_id"]').val();
                    const lessonDuration = $(this).find('input[name="lesson_duration"]').val();
                    
                    if (lessonId && lessonDuration) {
                        // حفظ مدة الدرس باستخدام AJAX
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'save_lesson_duration',
                                lesson_id: lessonId,
                                lesson_duration: lessonDuration,
                                _tutor_nonce: tutor_data.nonce
                            }
                        });
                    }
                });
                
                // حفظ مدة الدرس عند النقر على زر الحفظ
                $(document).on('click', '.tutor-lesson-modal-wrap .tutor-modal-submit-btn', function() {
                    const lessonId = $('.tutor-lesson-modal-wrap').find('input[name="lesson_id"]').val();
                    const lessonDuration = $('.tutor-lesson-modal-wrap').find('input[name="lesson_duration"]').val();
                    
                    if (lessonId && lessonDuration) {
                        // حفظ مدة الدرس باستخدام AJAX
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'save_lesson_duration',
                                lesson_id: lessonId,
                                lesson_duration: lessonDuration,
                                _tutor_nonce: tutor_data.nonce
                            }
                        });
                    }
                });
            });
            </script>
            <?php
        }
    }

    /**
     * حفظ قيمة مدة الدرس باستخدام خطاف tutor/modal_lesson_update
     */
    public function save_lesson_duration_on_modal_update($post_id, $post_data) {
        if (isset($post_data['_lesson_duration'])) {
            update_post_meta($post_id, '_lesson_duration', sanitize_text_field($post_data['_lesson_duration']));
        } elseif (isset($_POST['lesson_duration'])) {
            update_post_meta($post_id, '_lesson_duration', sanitize_text_field($_POST['lesson_duration']));
        }
    }

    /**
     * حفظ قيمة مدة الدرس عند إنشاء درس جديد
     */
    public function save_lesson_duration_on_create($post) {
        $post_id = is_object($post) ? $post->ID : (is_numeric($post) ? $post : 0);
        
        if ($post_id && isset($_POST['lesson_duration'])) {
            update_post_meta($post_id, '_lesson_duration', sanitize_text_field($_POST['lesson_duration']));
            
            // تحديث المدة الإجمالية للدورة
            $course_id = get_post_meta($post_id, '_tutor_course_id_for_lesson', true);
            if ($course_id) {
                $this->calculate_course_total_duration($course_id);
            }
        }
    }

    /**
     * حفظ البيانات عند تحديث الدرس
     */
    public function save_lesson_duration_on_update($post_ID) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (isset($_POST['lesson_duration'])) {
            update_post_meta($post_ID, '_lesson_duration', sanitize_text_field($_POST['lesson_duration']));
        }
    }

    /**
     * عرض مدة الدرس في نافذة مشاهدة الدرس المنبثقة
     */
    public function display_lesson_duration_in_popup() {
        $lesson_id = get_the_ID();
        $duration = get_post_meta($lesson_id, '_lesson_duration', true);
        
        if (!empty($duration)) {
            ?>
            <div class="tutor-lesson-duration-info">
                <span class="tutor-icon-clock"></span>
                <?php echo sprintf(__('مدة الدرس: %d دقيقة', 'tutor'), $duration); ?>
            </div>
            <?php
        }
    }

    /**
     * إضافة مدة الدرس إلى قائمة الدروس في صفحة الدورة التدريبية
     */
    public function add_duration_to_course_curriculum($html, $lesson) {
        if (is_object($lesson) && isset($lesson->ID)) {
            $lesson_id = $lesson->ID;
        } elseif (is_numeric($lesson)) {
            $lesson_id = $lesson;
        } else {
            return $html;
        }
        
        $duration = get_post_meta($lesson_id, '_lesson_duration', true);
        
        if (!empty($duration)) {
            $duration_html = ' <span class="tutor-lesson-duration" style="margin-left: 10px;">';
            $duration_html .= '<i class="tutor-icon-clock"></i> ';
            $duration_html .= sprintf(__('%d دقيقة', 'tutor'), $duration);
            $duration_html .= '</span>';
            
            // نضيف علامة الوقت قبل نهاية العنصر
            $pos = strpos($html, '</div>');
            if ($pos !== false) {
                $html = substr_replace($html, $duration_html . '</div>', $pos, 6);
            } else {
                $html .= $duration_html;
            }
        }
        
        return $html;
    }

    /**
     * إضافة CSS مخصص لتنسيق عرض مدة الدرس
     */
    public function add_lesson_duration_styles() {
        ?>
        <style type="text/css">
            .tutor-lesson-duration-info {
                display: inline-block;
                padding: 5px 10px;
                background-color: #f1f1f1;
                border-radius: 3px;
                margin-bottom: 20px;
                font-size: 14px;
            }
            
            .tutor-lesson-duration {
                display: inline-block;
                color: #727272;
                font-size: 13px;
            }
            
            .tutor-icon-clock {
                display: inline-block;
                margin-right: 5px;
            }
        </style>
        <?php
    }

    /**
     * حساب المدة الإجمالية للدورة التدريبية
     */
    public function calculate_course_total_duration($course_id) {
        $total_duration = 0;
        
        // نتحقق إذا كانت دالة tutor_utils متاحة
        if (function_exists('tutor_utils')) {
            // الحصول على جميع الدروس في الدورة
            $lessons = tutor_utils()->get_course_contents_by_id($course_id, 'lesson');
            
            if (is_array($lessons)) {
                foreach ($lessons as $lesson) {
                    $duration = get_post_meta($lesson->ID, '_lesson_duration', true);
                    if (!empty($duration) && is_numeric($duration)) {
                        $total_duration += intval($duration);
                    }
                }
            }
        } else {
            // طريقة بديلة للحصول على الدروس إذا كانت دالة tutor_utils غير متاحة
            $args = array(
                'post_type' => 'lesson',
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => '_tutor_course_id_for_lesson',
                        'value' => $course_id,
                        'compare' => '='
                    )
                ),
                'posts_per_page' => -1
            );
            
            $lessons = get_posts($args);
            
            if (!empty($lessons)) {
                foreach ($lessons as $lesson) {
                    $duration = get_post_meta($lesson->ID, '_lesson_duration', true);
                    if (!empty($duration) && is_numeric($duration)) {
                        $total_duration += intval($duration);
                    }
                }
            }
        }
        
        // تخزين المدة الإجمالية كمعلومات وصفية للدورة
        update_post_meta($course_id, '_course_total_duration', $total_duration);
        
        return $total_duration;
    }

    /**
     * تسجيل نقطة وصول AJAX
     */
    public function register_lesson_duration_ajax() {
        add_action('wp_ajax_save_lesson_duration', [$this, 'save_lesson_duration_ajax']);
    }
}

new NELC_Integration_Settings();