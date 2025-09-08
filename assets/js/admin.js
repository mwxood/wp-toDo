(function() {

    function editTask() {
        const dialog = document.getElementById("edit-task-dialog");
        const cancelBtn = document.getElementById("cancel-edit");

        document.querySelectorAll(".edit-task-button").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();

                document.getElementById("edit-task-id").value = this.dataset.id;
                document.getElementById("edit-task-title").value = this.dataset.title;
                document.getElementById("edit-task-description").value = this.dataset.description;

                dialog.showModal();
            });
        });

        if(!cancelBtn){
            return;
        }

        cancelBtn.addEventListener("click", function() {
            dialog.close();
        });
    }

    function deleteTask() {
        const deleteButtons = document.querySelectorAll(".delete-task-button");
        const dialog = document.getElementById("delete-confirm-dialog");
        const taskIdField = document.getElementById("task-id-field");
        const cancelBtn = document.getElementById("cancel-delete");
        const nonceField = document.querySelector("#delete-task-form input[name='delete_task_nonce']");

        deleteButtons.forEach(function(button) {
            button.addEventListener("click", function(e) {
                e.preventDefault();

                const taskId = button.getAttribute("data-task-id");
                taskIdField.value = taskId;

                nonceField.value = button.getAttribute("data-nonce");

                dialog.showModal();
            });
        });

        if (cancelBtn) {
            cancelBtn.addEventListener("click", function() {
                dialog.close();
            });
        }
    }

    function init() {
        editTask();
        deleteTask();
    }

    document.addEventListener("DOMContentLoaded", function() {
        init();
    });
})();


