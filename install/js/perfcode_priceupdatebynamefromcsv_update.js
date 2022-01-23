'use strict';

window.addEventListener('load', function () {
    document.getElementById('open_file_dialog_button').onclick = OpenFileDialog;

    document.getElementById('start-update-button').addEventListener('click', function () {
        BX.adjust(BX('update-info'), {html: ''});
        const requestedPage = document.getElementById('requested-page').value.trim();
        prepareUpdate(requestedPage);
    });
});

function prepareUpdate(url) {
    const filepath = document.getElementById('selected_file_path').value.trim();

    const params = {
        filepath: filepath
    }

    fetch(`${url}?action=checkfileexists`, {
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
            if (data.result && data.result === 'yes') {
                saveParams(url, params);
            } else {
                showMessage(url, 'ERROR', 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_MISS', {}, 'update-info');
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
        }
    );
}

function saveParams(url, params) {
    let entryId = document.getElementById('params-entry-id').value.trim();
    entryId = parseInt(entryId);
    if (Number.isNaN(entryId)) {
        params.entryid = 0;
    } else {
        params.entryid = entryId;
    }

    fetch(`${url}?action=saveparams`, {
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
            if (data.result === 'fail') {
                showMessage(url, 'ERROR', 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PARAMS_ERROR', {}, 'update-info');
            } else {
                const entryId = data.result;
                document.getElementById('params-entry-id').value = entryId;
                // updateProducts(url, params);
            }
        }
    ).catch(
        (error) => {
            // console.error(error);
        }
    );
}

function updateProducts(url, params) {
    fetch(`${url}?action=update`, {
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
            // console.log(data);
        }
    ).catch(
        (error) => {
            // console.error(error);
        }
    );
}
