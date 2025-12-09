document.addEventListener("DOMContentLoaded", function () {
    if (typeof Tutor !== "undefined" && Tutor.CourseBuilder) {

        // Register a textarea field
        Tutor.CourseBuilder.Basic.registerField("after_description", {
            name: "_telegram_url",
            type: "text",
            label: "Telegram URL",
            placeholder: "Write here...",
            priority: 20,
        });

        Tutor.CourseBuilder.Curriculum.Lesson.registerField("bottom_of_sidebar", {
            name: "_lesson_duration",
            type: "number",
            label: "Lesson Duration (minutes)",
            priority: 5,
        });
    }
});
