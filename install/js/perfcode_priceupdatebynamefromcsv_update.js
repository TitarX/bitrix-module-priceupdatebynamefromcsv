'use strict';

window.addEventListener('load', function () {
    document.getElementById('open_file_dialog_button').onclick = OpenFileDialog;

    document.getElementById('start-update-button').addEventListener('click', function () {
        BX.adjust(BX('update-info'), {html: ''});
        const requestedPage = document.getElementById('requested-page').value.trim();
        const waitSpinner = BX.showWait('update-info');
        prepareUpdate(requestedPage, waitSpinner);
    });
});

function prepareUpdate(url, waitSpinner) {
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
                saveParams(url, params, waitSpinner);
            } else {
                BX.closeWait('update-info', waitSpinner);
                showMessage(url, 'ERROR', 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_MISS', {}, 'update-info');
            }
        }
    ).catch(
        (error) => {
            BX.closeWait('update-info', waitSpinner);
            // console.error(error);
        }
    );
}

function saveParams(url, params, waitSpinner) {
    let entryId = document.getElementById('params-entry-id').value.trim();
    entryId = parseInt(entryId);
    if (Number.isNaN(entryId)) {
        params.entryid = 0;
    } else {
        params.entryid = entryId;
    }

    params.productname = document.getElementById('product-name-csv').value.trim();
    params.price = document.getElementById('price-csv').value.trim();
    params.currency = document.getElementById('currency-csv').value.trim();
    params.iblock = document.getElementById('iblock-id').value.trim();
    params.manufacturer = document.getElementById('manufacturer-property').value.trim();

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
                BX.closeWait('update-info', waitSpinner);
                showMessage(url, 'ERROR', 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PARAMS_ERROR', {}, 'update-info');
            } else {
                const entryId = data.result;
                document.getElementById('params-entry-id').value = entryId;
                updateProducts(url, params, waitSpinner);
            }
        }
    ).catch(
        (error) => {
            BX.closeWait('update-info', waitSpinner);
            // console.error(error);
        }
    );
}

function updateProducts(url, params, waitSpinner) {
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
            BX.closeWait('update-info', waitSpinner);
            if (data.result === 'fail') {
                showMessage(url, 'ERROR', data.error, data.errorargs, 'update-info');
            } else if (data.result === 'success') {
                showMessage(url, 'OK', 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_SUCCESS', {}, 'update-info');
                showMessage(url, 'OK', 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_COUNTS', data.updatecounts, 'update-info');
            } else {
                console.log(data);
            }
        }
    ).catch(
        (error) => {
            BX.closeWait('update-info', waitSpinner);
            // console.error(error);
        }
    );
}
