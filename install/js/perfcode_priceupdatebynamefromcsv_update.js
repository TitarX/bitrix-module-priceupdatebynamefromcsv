'use strict';

window.addEventListener('load', function () {
    // document.getElementById('open_file_dialog_button').onclick = OpenFileDialog;

    const temp = document.getElementById('start-update-button');
    console.log(temp);

    document.getElementById('start-update-button').addEventListener('click', function () {
        const requestedPage = document.getElementById('requested-page').value.trim();
        prepareUpdate(requestedPage);
    });
});

function prepareUpdate(url) {
    const filepath = document.getElementById('selected_file_path').value.trim();

    const params = {
        filepath: filepath
    }

    fetch(`${url}&action=checkfileexists`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(params)
    }).then(
        response => response.json()
    ).then(
        (data) => {
            if (data.result) {
                // console.log(data.result);
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
        }
    );
}

function saveParams(params) {
    fetch(`${url}&action=saveparams`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(params)
    }).then(
        response => response.json()
    ).then(
        (data) => {
            if (data.result) {
                // console.log(data.result);
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
        }
    );
}
