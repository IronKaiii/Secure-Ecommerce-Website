// Reference: https://codepen.io/joezimjs/pen/yPWQbd

let dropZone = document.getElementById("drop_file_zone");

// prevent default action and propagated to other div
function preventDefault (e) {
    e.preventDefault();
    e.stopPropagation();
}

function dropping(e) {
    dropZone.classList.add('indicator')
}

function notDropping(e) {
    dropZone.classList.remove('indicator')
}
  

// prevent drag action
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefault);
    document.body.addEventListener(eventName, preventDefault);
});

// add indicator to inform user that he is dropping on the right position
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, dropping);
});
  
['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, notDropping);
});

// handling the file dropped
dropZone.addEventListener("drop", fileHandler);

function fileHandler(e) {
    selectfile.files = e.dataTransfer.files;
    if(e.dataTransfer.files.length > 0){
        var src = URL.createObjectURL(e.dataTransfer.files[0]);
        var preview = document.getElementById("previewImg");
        preview.src = src;
        preview.onload = function() {
            URL.revokeObjectURL(src) // free memory
        }
    }
}

// preview image if the user upload file using button
function previewImage(event) {
    if(event.target.files.length > 0){
        var src = URL.createObjectURL(event.target.files[0]);
        var preview = document.getElementById("previewImg");
        preview.src = src;
        preview.onload = function() {
            URL.revokeObjectURL(src) // free memory
        }
    }
}
