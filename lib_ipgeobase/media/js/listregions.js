document.addEventListener("DOMContentLoaded", function () {

    let listRegions = document.querySelectorAll('.list-region');
    let listAutocomplete = [];
    let ids = [];

    for (let i = 0; i < listRegions.length; i++) {
        createBlockRegionAutoComplete(listRegions[i]);

        if(listRegions[i].value !== '') {
            ids.push(listRegions[i].value);
        }

        if((i+1) === listRegions.length) {
            loadByIds(ids);
        }
    }

    function createBlockRegionAutoComplete(element) {
        let completeWrap = document.createElement('div');
        let completeInput =  document.createElement('input');
        let errorWrap = document.createElement('div');
        let interval;
        completeWrap.appendChild(completeInput);
        element.parentElement.appendChild(completeWrap);
        element.parentElement.insertBefore(errorWrap, element);

        listAutocomplete.push(new Autocomplete(completeInput, {
            url: '/index.php?option=com_ajax&plugin=findcityorregions&group=system&format=raw&action=getRegionsSearch',
            param: 'q',
            label: 'text',
            el: element,
            input: completeInput,
            select: function(item) {
                this.el.value = item.value;
                errorWrap.style.display = 'none';
                clearTimeout(interval);
            }
        }));

        completeWrap.classList.add('autocomplete-wrap');
        completeInput.setAttribute('type', 'text');
        completeInput.setAttribute('autocomplete', 'new-input-' + randomInteger(11111, 99999));
        completeInput.setAttribute('placeholder', 'Начните вводить название области...');
        completeInput.setAttribute('class', element.getAttribute('class'));
        element.style.display = 'none';
        errorWrap.classList.add("alert");
        errorWrap.classList.add("alert-danger");
        errorWrap.style.display = 'none';

        completeInput.addEventListener("focusout", function (ev) {

            interval = setTimeout(function () {
                if (element.value === '') {
                    errorWrap.innerHTML = 'Выберите значение из списка';
                    errorWrap.style.display = 'block';
                    completeInput.value = '';
                } else {
                    errorWrap.style.display = 'none';
                }
            }, 300);

        });

        completeInput.addEventListener("paste", function (ev) {
            //ev.preventDefault();
        });

    }


    function loadByIds(ids) {

        if(ids.length === 0) {
            return;
        }

        let xmlhttp = new XMLHttpRequest();
        let url = "/index.php?option=com_ajax&plugin=findcityorregions&group=system&format=raw&action=getRegionsByIDS&ids=" + ids.join(',');

        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                let responseRegions = JSON.parse(this.responseText);
                let idsForRegion = {};

                for (let i = 0; i < responseRegions.length; i++) {
                    idsForRegion[parseInt(responseRegions[i].value)] = responseRegions[i].text;
                }

                for (let i = 0; i < listAutocomplete.length; i++) {
                    listAutocomplete[i].options.input.value = idsForRegion[parseInt(listAutocomplete[i].options.el.value)];
                }

            }
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();

    }


    function randomInteger(min, max) {
        let rand = min - 0.5 + Math.random() * (max - min + 1);
        rand = Math.round(rand);
        return rand;
    }


    jQuery(document).on('subform-row-add', function(event, row){

        if(row === undefined) {
            return false;
        }

        let inputs = row.querySelectorAll('input');

        for(let i=0;i<inputs.length;i++) {
            if(inputs[i].classList.contains("list-region")) {
                createBlockRegionAutoComplete(inputs[i]);
                row.querySelector('.autocomplete').focus();
            }
        }

    });



});