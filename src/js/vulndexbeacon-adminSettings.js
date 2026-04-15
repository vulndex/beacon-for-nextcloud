document.addEventListener('DOMContentLoaded', () => {
    (function () {
        const saveBtn = document.getElementById('vulndexbeacon-save');
        const sendBtn = document.getElementById('vulndexbeacon-send');
        const statusSave = document.getElementById('vulndexbeacon-statusSave');
        const statusSend = document.getElementById('vulndexbeacon-statusSend');
        const apiKey = document.getElementById('vulndexbeacon-apiKey');
        const saveIndicator = document.getElementById('savebtn_loader');
        const sendIndicator = document.getElementById('sendbtn_loader');


        const disableAllInput = function () {
            saveBtn.setAttribute("disabled", "disabled");
            sendBtn.setAttribute("disabled", "disabled");
            apiKey.setAttribute("disabled", "disabled");
        }
        const enableAllInput = function () {
            saveBtn.removeAttribute("disabled");

            apiKey.removeAttribute("disabled");
            if (apiKey.getAttribute("data-set") === "yes") {
                sendBtn.removeAttribute("disabled");
            }
        }

        enableAllInput();

        function setStatusSave(message, error) {
            statusSave.textContent = message;
            statusSave.style.color = error ? 'red' : 'green';
        }

        function setStatusSend(message, error) {
            statusSend.textContent = message;
            statusSend.style.color = error ? 'red' : 'green';
        }

        saveBtn.addEventListener('click', function () {
            const apiKeyValue = apiKey.value;

            const data = new FormData();
            data.append('apiKey', apiKeyValue);
            disableAllInput();
            saveIndicator.classList.remove("hidden");
            setStatusSave("", false);

            fetch(OC.generateUrl('/apps/vulndexbeacon/admin/apikey'), {
                method: 'POST',
                body: data,
                headers: {
                    'requesttoken': OC.requestToken
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {
                    if (json.success) {
                        setStatusSave(json.message, false);

                        apiKey.setAttribute("data-set", json.add ? 'yes' : 'no');
                        apiKey.value = "";
                        apiKey.placeholder = json.add ? t('vulndexbeacon', '✓ API-Schlüssel ist gesetzt') : t('vulndexbeacon', 'API-Schlüssel eingeben')

                    } else {
                        setStatusSave("✗ " + json.message || t('vulndexbeacon', 'Fehler beim Speichern'), true);
                    }
                    enableAllInput();
                    saveIndicator.classList.add("hidden");
                })
                .catch(function () {
                    setStatusSave(t('vulndexbeacon', 'Netzwerkfehler beim Speichern'), true);
                    enableAllInput();
                    saveIndicator.classList.add("hidden");
                });
        });

        sendBtn.addEventListener('click', function () {
            disableAllInput();
            sendIndicator.classList.remove("hidden");
            setStatusSend("", false);
            fetch(OC.generateUrl('/apps/vulndexbeacon/admin/send-now'), {
                method: 'POST',
                headers: {
                    'requesttoken': OC.requestToken
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (json) {

                    const lastSend = document.getElementById("vulndex-lastsend");
                    const lastStatus = document.getElementById("vulndex-laststatus");
                    const lastStatusColor = document.getElementById("vulndex-laststatuscolor");
                    const lastResponse = document.getElementById("vulndex-lastresponse");


                    lastSend.textContent = '';
                    lastStatus.textContent = '';
                    lastResponse.textContent = '';

                    lastSend.appendChild(document.createTextNode(json.lastReport));
                    lastStatus.appendChild(document.createTextNode(json.status ? t('vulndexbeacon', '✓ Erfolgreich') : t('vulndexbeacon', '✗ Fehler')));
                    lastResponse.appendChild(document.createTextNode(json.serverResponse));

                    if (json.success) {
                        lastStatusColor.style.color = "green";
                        setStatusSend(t('vulndexbeacon', 'Übertragung erfolgreich'), false);
                    } else {

                        lastStatusColor.style.color = "red";

                        setStatusSend(t('vulndexbeacon', 'Fehler: ') + json.message, true);
                    }
                    enableAllInput();
                    sendIndicator.classList.add("hidden");
                })
                .catch(function () {
                    setStatusSend(t('vulndexbeacon', 'Netzwerkfehler beim Senden'), true);
                    enableAllInput();
                    sendIndicator.classList.add("hidden");
                });
        });
    })();
});
