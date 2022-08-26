function error_on_load (element) {
    element = $(element).closest('div.image_wrapper');
    let id = element.attr('id');
    element.replaceWith('<div id="' + id + '" class="image_wrapper"><a href="/img?id=' + id + '"><div class="no_image">Image not found.</div></a></div>');
}

function not_found (element) {
    element = $(element);
    element.replaceWith('<div class="showcase not_found">Image not found</div>')
}

function toggle_faces () {
    let faces = $('div.faces');
    if (faces.css('display') === 'none') {
        faces.css('display', 'block');
    } else {
        faces.css('display', 'none');
    }
}

function load_faces (faces, og_h, og_w) {
    let rectangles = $('<div class="faces"></div>')
    let showcase =  $('div.showcase');
    let img = showcase.find('img');
    let new_h = img.height();
    let new_w = img.width();
    let trans_h = new_h / og_h;
    let trans_w = new_h / og_h;
    rectangles.css('width', new_w).css('height', new_h).css('display', 'none');
    showcase.append(rectangles);
    for (let i = 0; i < faces.length; i++) {
        let f = faces[i];
        let face = $('<div class="face"></div>')
        face.css('left', trans_h * f.x).css('top', trans_w * f.y).css('width', trans_w * f.w + 'px').css('height', trans_h * f.h + 'px')
        rectangles.append(face);
    }
}

function get_filters () {
    let inputs = {};
    $('.filters input').each(function(){
            let input = $(this);
            let value = input.val().trim();
            if (value !== '') {
                inputs[input.attr('id')] = value;
            }

        }
    );

    $('.filters select').each(function(){
            let input = $(this);
            let value = input.find(":selected").val().trim();
            if (value !== '') {
                inputs[input.attr('id')] = value;
            }
        }
    );

    return inputs;
}

function filter () {
    $('button.submit').html('<i class="fas fa-spinner fa-pulse"></i>');
    let inputs = get_filters();

    $.ajax({
        url: '/api/filter.php',
        method: 'POST',
        data: inputs,
        success: function(data) {
            $('button.submit').html('Filter');
            window.location.href = '/';
        },
        error: function(data) {
            alert('Error while filtering:\n\n' + data.responseText);
        }
    });
}

function download_data () {
    let id = $('main').attr('id');

    let download = window.open('/api/download.php?id=' + id, '_blank');

    window.setTimeout(function(){
        download.close();
    }, 1000);
}

function load_filter () {
    let name = $('select.filter_load :selected').val();

    if (name === '') {
        return;
    }

    $.ajax({
        url: '/api/load_filter.php',
        method: 'POST',
        data: {
            name: name
        },
        success: function(data) {
            window.location.href = '/';
        },
        error: function(data) {
            alert('Error while loading filter:\n\n' + data.responseText);
        }
    });
}

function save_filter () {
    $('button.save').html('<i class="fas fa-spinner fa-pulse"></i>');
    let name = $('input.filter_save').val();

    if (name === '') {
        alert("Name can't be ''.");
        return;
    }

    let inputs = get_filters();

    $.ajax({
        url: '/api/save_filter.php',
        method: 'POST',
        data: {
            name: name,
            inputs: inputs
        },
        success: function(data) {
            $('button.save').html('Save');
            $('select.filter_load :selected').prop('selected', false);
            let existing_option = $('select.filter_load option[value="' + name + '"]');
            if (existing_option.length > 0) {
                existing_option.prop('selected', true);
            } else {
                $('select.filter_load').append('<option value="' + name + '" selected>' + name + '</option>');
            }
        },
        error: function(data) {
            alert('Error while saving filter:\n\n' + data.responseText);
        }
    });
}