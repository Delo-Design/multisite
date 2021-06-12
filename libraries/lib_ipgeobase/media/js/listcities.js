document.addEventListener("DOMContentLoaded", function () {

    let listCities = document.querySelectorAll('.list-city');
    let listAutocomplete = [];
    let ids = [];

    for (let i = 0; i < listCities.length; i++) {
        createBlockCityAutoComplete(listCities[i]);

        if(listCities[i].value !== '') {
            ids.push(listCities[i].value);
        }

        if((i+1) === listCities.length) {
            loadByIds(ids);
        }
    }

    function createBlockCityAutoComplete(element) {
        let completeWrap = document.createElement('div');
        let completeInput =  document.createElement('input');
        let errorWrap = document.createElement('div');
        let interval;
        completeWrap.appendChild(completeInput);
        element.parentElement.appendChild(completeWrap);
        element.parentElement.insertBefore(errorWrap, element);

        listAutocomplete.push(new Autocomplete(completeInput, {
            url: '/index.php?option=com_ajax&plugin=findcityorregions&group=system&format=raw&action=getCitiesSearch',
            param: 'q',
            label: 'text',
            el: element,
            input: completeInput,
            select: function(item) {
                this.el.value = item.value;
                errorWrap.style.display = 'none';
            }
        }));

        completeWrap.classList.add('autocomplete-wrap');
        completeInput.setAttribute('type', 'text');
        completeInput.setAttribute('autocomplete', 'new-input-' + randomInteger(11111, 99999));
        completeInput.setAttribute('placeholder', 'Начните вводить название города...');
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
        let url = "/index.php?option=com_ajax&plugin=findcityorregions&group=system&format=raw&action=getCitiesByIDS&ids=" + ids.join(',');

        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                let responseCities = JSON.parse(this.responseText);
                let idsForCity = {};

                for (let i = 0; i < responseCities.length; i++) {
                    idsForCity[parseInt(responseCities[i].value)] = responseCities[i].text;
                }

                for (let i = 0; i < listAutocomplete.length; i++) {
                    listAutocomplete[i].options.input.value = idsForCity[parseInt(listAutocomplete[i].options.el.value)];
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
            if(inputs[i].classList.contains("list-city")) {
                createBlockCityAutoComplete(inputs[i]);
                row.querySelector('.autocomplete').focus();
            }
        }


    });



});