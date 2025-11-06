document.addEventListener("DOMContentLoaded", function () {
    if (typeof Tutor !== "undefined" && Tutor.CourseBuilder) {
        Tutor.CourseBuilder.Curriculum.Lesson.registerField("bottom_of_sidebar", {
            name: "_lesson_duration",
            type: "number",
            label: "Lesson Duration (minutes)",
            priority: 5,
        });
    }
});
