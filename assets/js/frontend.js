(function() {
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".status-btn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();

                let taskId = this.dataset.id;
                let newStatus = this.dataset.status;

                let formData = new FormData();
                formData.append("action", "edit_front_task");
                formData.append("task_id", taskId);
                formData.append("status", newStatus);
                formData.append("nonce", wpTodoAjax.nonce);

                fetch(wpTodoAjax.ajaxurl, {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); 
                    } else {
                        alert("Error updating task");
                    }
                });
            });
        });
    });
})();
