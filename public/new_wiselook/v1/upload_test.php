<?php
// test.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Media Tester</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .upload-container {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        #file-input {
            display: none;
        }
        .upload-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        .upload-btn:hover {
            background-color: #45a049;
        }
        #response {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        #file-list {
            margin-top: 10px;
            text-align: left;
        }
        .progress-container {
            width: 100%;
            background-color: #f1f1f1;
            margin-top: 10px;
        }
        .progress-bar {
            width: 0%;
            height: 30px;
            background-color: #4CAF50;
            text-align: center;
            line-height: 30px;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Media Upload Tester</h1>
    <p>Test the upload limits of your server</p>
    
    <div class="upload-container">
        <form id="upload-form" enctype="multipart/form-data">
            <input type="file" id="file-input" name="media[]" multiple>
            <button type="button" class="upload-btn" id="select-files">Select Files</button>
            <div id="file-list"></div>
            <button type="submit" class="upload-btn" id="upload-button">Upload Files</button>
            <div class="progress-container">
                <div class="progress-bar" id="progress-bar">0%</div>
            </div>
        </form>
    </div>
    
    <div id="response"></div>
    
    <script>
    $(document).ready(function() {
        // Handle file selection
        $('#select-files').click(function() {
            $('#file-input').click();
        });
        
        // Show selected files
        $('#file-input').change(function() {
            const files = this.files;
            let fileList = '<strong>Selected Files:</strong><ul>';
            
            for (let i = 0; i < files.length; i++) {
                fileList += `<li>${files[i].name} (${formatFileSize(files[i].size)})</li>`;
            }
            
            fileList += '</ul>';
            $('#file-list').html(fileList);
        });
        
        // Handle form submission
        $('#upload-form').submit(function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const files = $('#file-input')[0].files;
            
            if (files.length === 0) {
                showResponse('error', 'Please select at least one file');
                return;
            }
            
            $('#upload-button').prop('disabled', true);
            $('#progress-bar').css('width', '0%').text('0%');
            
            $.ajax({
                url: 'upload_media_test.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new XMLHttpRequest();
                    
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            $('#progress-bar').css('width', percentComplete + '%')
                                            .text(percentComplete + '%');
                        }
                    }, false);
                    
                    return xhr;
                },
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (data.success) {
                            showResponse('success', data.message);
                            
                            if (data.media_name && data.media_name.length > 0) {
                                let uploadedFiles = '<strong>Uploaded Files:</strong><ul>';
                                data.media_name.forEach(file => {
                                    uploadedFiles += `<li>${file}</li>`;
                                });
                                uploadedFiles += '</ul>';
                                $('#response').append(uploadedFiles);
                            }
                        } else {
                            showResponse('error', data.message);
                            
                            if (data.errors && data.errors.length > 0) {
                                let errorList = '<strong>Errors:</strong><ul>';
                                data.errors.forEach(error => {
                                    errorList += `<li>${error}</li>`;
                                });
                                errorList += '</ul>';
                                $('#response').append(errorList);
                            }
                        }
                    } catch (e) {
                        showResponse('error', 'Invalid server response: ' + e.message);
                    }
                },
                error: function(xhr, status, error) {
                    showResponse('error', 'AJAX Error: ' + error);
                },
                complete: function() {
                    $('#upload-button').prop('disabled', false);
                }
            });
        });
        
        function showResponse(type, message) {
            const $response = $('#response');
            $response.removeClass('success error')
                     .addClass(type)
                     .html(`<strong>${type.toUpperCase()}:</strong> ${message}`)
                     .show();
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    });
    </script>
</body>
</html>