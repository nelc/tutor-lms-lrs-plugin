<?php
// Don't allow direct access
if (!defined('ABSPATH')) {
    exit;
}

class NELC_National_ID {
    
    public function __construct() {
        // Add field to WordPress registration form
        add_action('register_form', array($this, 'add_national_id_field'));
        
        // Validate field in WordPress registration
        add_filter('registration_errors', array($this, 'validate_national_id_field'), 10, 3);
        
        // Save field during WordPress registration
        add_action('user_register', array($this, 'save_national_id_field'));
        
        // Add field to user profile (admin & frontend)
        add_action('show_user_profile', array($this, 'add_national_id_to_profile'));
        add_action('edit_user_profile', array($this, 'add_national_id_to_profile'));
        
        // Save field from user profile
        add_action('personal_options_update', array($this, 'save_profile_national_id'));
        add_action('edit_user_profile_update', array($this, 'save_profile_national_id'));
        
        // WooCommerce integration
        add_action('woocommerce_edit_account_form', array($this, 'add_national_id_to_woocommerce'));
        add_action('woocommerce_save_account_details', array($this, 'save_woocommerce_national_id'));
        
        // Tutor LMS integration
        add_action('tutor_profile_edit_input_after', array($this, 'add_national_id_to_tutor_profile'));
        add_action('tutor_profile_update_before', array($this, 'save_tutor_national_id'));
        
        // Tutor LMS registration form
        add_action('tutor_student_reg_form_fields_before', array($this, 'add_national_id_to_tutor_registration'));
        add_action('tutor_register_errors', array($this, 'validate_tutor_national_id_field'));
        add_filter('tutor_register_user_data', array($this, 'process_tutor_registration_national_id'));
    }
    
    /**
     * Add National ID field to WordPress registration form
     */
    public function add_national_id_field() {
        ?>
        <p>
            <label for="nelc_national_id"><?php _e('National ID Number', 'tutor-nelc-integration'); ?><br/>
            <input type="text" name="nelc_national_id" id="nelc_national_id" class="input" value="<?php echo esc_attr(isset($_POST['nelc_national_id']) ? $_POST['nelc_national_id'] : ''); ?>" size="25" /></label>
        </p>
        <?php
    }
    
    /**
     * Validate National ID field in WordPress registration
     */
    public function validate_national_id_field($errors, $sanitized_user_login, $user_email) {
        if (empty($_POST['nelc_national_id'])) {
            $errors->add('nelc_national_id_error', __('<strong>ERROR</strong>: Please enter your National ID number.', 'tutor-nelc-integration'));
        }
        return $errors;
    }
    
    /**
     * Save National ID field during WordPress registration
     */
    public function save_national_id_field($user_id) {
        if (isset($_POST['nelc_national_id'])) {
            update_user_meta($user_id, 'nelc_national_id', sanitize_text_field($_POST['nelc_national_id']));
        }
    }
    
    /**
     * Add National ID field to user profile (admin & frontend)
     */
    public function add_national_id_to_profile($user) {
        ?>
        <h3><?php _e('National ID Information', 'tutor-nelc-integration'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="nelc_national_id"><?php _e('National ID Number', 'tutor-nelc-integration'); ?></label></th>
                <td>
                    <input type="text" name="nelc_national_id" id="nelc_national_id" value="<?php echo esc_attr(get_user_meta($user->ID, 'nelc_national_id', true)); ?>" class="regular-text" /><br />
                    <span class="description"><?php _e('Enter your National ID number.', 'tutor-nelc-integration'); ?></span>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save National ID field from user profile
     */
    public function save_profile_national_id($user_id) {
        if (current_user_can('edit_user', $user_id) && isset($_POST['nelc_national_id'])) {
            update_user_meta($user_id, 'nelc_national_id', sanitize_text_field($_POST['nelc_national_id']));
        }
    }
    
    /**
     * Add National ID field to WooCommerce account page
     */
    public function add_national_id_to_woocommerce() {
        $user_id = get_current_user_id();
        $national_id = get_user_meta($user_id, 'nelc_national_id', true);
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="nelc_national_id"><?php _e('National ID Number', 'tutor-nelc-integration'); ?> <span class="required">*</span></label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="nelc_national_id" id="nelc_national_id" value="<?php echo esc_attr($national_id); ?>" />
        </p>
        <?php
    }
    
    /**
     * Save National ID field from WooCommerce account page
     */
    public function save_woocommerce_national_id($user_id) {
        if (isset($_POST['nelc_national_id'])) {
            update_user_meta($user_id, 'nelc_national_id', sanitize_text_field($_POST['nelc_national_id']));
        }
    }
    
    /**
     * Add National ID field to Tutor LMS profile page
     */
    public function add_national_id_to_tutor_profile() {
        $user_id = get_current_user_id();
        $national_id = get_user_meta($user_id, 'nelc_national_id', true);
        ?>
        <div class="tutor-form-row">
            <div class="tutor-form-col-12">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('National ID Number', 'tutor-nelc-integration'); ?>
                    </label>
                    <input type="text" name="nelc_national_id" value="<?php echo esc_attr($national_id); ?>" placeholder="<?php _e('Enter your National ID number', 'tutor-nelc-integration'); ?>">
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save National ID field from Tutor LMS profile page
     */
    public function save_tutor_national_id($user_id) {
        if (isset($_POST['nelc_national_id'])) {
            update_user_meta($user_id, 'nelc_national_id', sanitize_text_field($_POST['nelc_national_id']));
        }
    }
    
    /**
     * Add National ID field to Tutor LMS registration form
     */
    public function add_national_id_to_tutor_registration() {
        ?>
        <div class="tutor-form-row">
            <div class="tutor-form-col-12">
                <div class="tutor-form-group">
                    <label>
                        <?php _e('National ID Number', 'tutor-nelc-integration'); ?>
                    </label>
                    <input type="text" name="nelc_national_id" value="<?php echo isset($_POST['nelc_national_id']) ? esc_attr($_POST['nelc_national_id']) : ''; ?>" placeholder="<?php _e('Enter your National ID number', 'tutor-nelc-integration'); ?>">
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Validate National ID field in Tutor LMS registration
     */
    public function validate_tutor_national_id_field($errors) {
        if (empty($_POST['nelc_national_id'])) {
            $errors['nelc_national_id_error'] = __('National ID Number is required', 'tutor-nelc-integration');
        }
        return $errors;
    }
    
    /**
     * Process National ID field in Tutor LMS registration
     */
    public function process_tutor_registration_national_id($user_data) {
        if (isset($_POST['nelc_national_id'])) {
            $user_data['nelc_national_id'] = sanitize_text_field($_POST['nelc_national_id']);
        }
        return $user_data;
    }
}

// Initialize the plugin
new NELC_National_ID();