jQuery(document).ready(function($){

    jQuery( '#tutor_notify_action_check' ).on( 'click', function( event ) {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'tutor_notify_action',
            },
            success: function(response) {
                if(response.success){
                    iziToast.success({
                        title: 'OK',
                        message: response.data,
                    });
                }else{
                    iziToast.error({
                        title: 'Error',
                        message: response.data,
                    });
                }

                console.log('response', response)
                
            },
            error: function(error) {
                console.log('error',error);
            }
        });
    })


    if( window.location.pathname.match(/\/courses\//) || window.location.pathname.match(/\/lessons\//) ){

        var video = document.querySelector("video");

        if (!video) {
            return;
        }
        // منع التنفيذ المتكرر
        if (video.dataset.trackingInitialized) {
            return;
        }
        video.dataset.trackingInitialized = "true";


        let isDataSent = false;

        if (isDataSent) {
            console.log("🚫 الحدث تم إرساله مسبقًا، لن يتم إرساله مرة أخرى.");
            return;
        }

        let eventSent = false;

        video.addEventListener("timeupdate", function trackProgress() {
            if (!video.duration || eventSent || video.dataset.eventTriggered) return;

            let watched = (video.currentTime / video.duration) * 100;

            if (watched >= 80) {
                console.log("🚀 إرسال حدث مشاهدة الفيديو إلى ال api...");

                eventSent = true;
                video.dataset.eventTriggered = "true";

                let durationISO = formatDuration(video.duration);
                console.log('durationISO: ', durationISO);

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'mark_video_watched',
                        lesson_id: tutorLessonData.lesson_id,
                        course_id: tutorLessonData.course_id,
                        duration: durationISO,
                    },
                    success: function(response) {
                        console.log("✅ حدث المشاهدة تم إرساله بنجاح!", response);
                        if(typeof iziToast !== "undefined") {
                            iziToast.success({
                                title: 'OK',
                                message: response.data,
                            });
                        }
                    }
                });

            }
        });


        function formatDuration(seconds) {
            seconds = Math.max(1, Math.floor(seconds));
        
            let hours = Math.floor(seconds / 3600);
            let minutes = Math.floor((seconds % 3600) / 60);
            let secs = seconds % 60;
        
            return `PT${hours}H${minutes}M${secs}S`;
        }
    }
});
