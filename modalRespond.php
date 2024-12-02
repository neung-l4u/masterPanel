<style>
    .modal-confirm {
        color: #434e65;
        width: 525px;
    }
    .modal-confirm .modal-content {
        padding: 20px;
        font-size: 16px;
        border-radius: 5px;
        border: none;
    }
    .modal-confirm .modal-header {
        background: #47c9a2;
        border-bottom: none;
        position: relative;
        text-align: center;
        margin: -20px -20px 0;
        border-radius: 5px 5px 0 0;
        padding: 35px;
    }
    .modal-confirm h4 {
        text-align: center;
        font-size: 36px;
        margin: 10px 0;
    }
    .modal-confirm .form-control, .modal-confirm .btn {
        min-height: 40px;
        border-radius: 3px;
    }
    .modal-confirm .close {
        position: absolute;
        top: 15px;
        right: 15px;
        color: #fff;
        text-shadow: none;
        opacity: 0.5;
    }
    .modal-confirm .close:hover {
        opacity: 0.8;
    }
    .modal-confirm .icon-box {
        color: #fff;
        width: 95px;
        height: 95px;
        display: inline-block;
        border-radius: 50%;
        z-index: 9;
        border: 5px solid #fff;
        padding: 15px;
        text-align: center;
    }
    .modal-confirm .icon-box i {
        font-size: 64px;
        margin: -4px 0 0 -4px;
    }
    .modal-confirm.modal-dialog {
        margin-top: 80px;
    }
    .modal-confirm .btn, .modal-confirm .btn:active {
        color: #fff;
        border-radius: 4px;
        background: #eeb711 !important;
        text-decoration: none;
        transition: all 0.4s;
        line-height: normal;
        border-radius: 30px;
        margin-top: 10px;
        padding: 6px 20px;
        border: none;
    }
    .modal-confirm .btn:hover, .modal-confirm .btn:focus {
        background: #eda645 !important;
        outline: none;
    }
    .modal-confirm .btn span {
        margin: 1px 3px 0;
        float: left;
    }
    .modal-confirm .btn i {
        margin-left: 1px;
        font-size: 20px;
        float: right;
    }
    .trigger-btn {
        display: inline-block;
        margin: 100px auto;
    }
</style>
<!-- Modal Response-->
<div class="modal fade" id="modalResponse" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalRespondLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="respondSuccess">
                <div class="modal-header bg-success d-flex flex-column">
                    <div class="w-100 d-flex flex-row justify-content-end">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="icon-box w-100 d-flex flex-row justify-content-center">
                        <img src="assets/img/icon-success.svg" alt="success" width="104">
                    </div>
                </div>
                <div class="modal-body text-center">
                    <h4>Great!</h4>
                    <p class="text-success fw-bold">The data has been successfully saved.</p>
                    <p>You can close this popup to check for new information immediately.</p>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Understood</button>
                </div>
            </div>

            <div class="respondFail">
                <div class="modal-header bg-danger d-flex flex-column">
                    <div class="w-100 d-flex flex-row justify-content-end">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="icon-box w-100 d-flex flex-row justify-content-center">
                        <img src="assets/img/icon-fail.svg" alt="fail" width="104">
                    </div>
                </div>
                <div class="modal-body text-center">
                    <h4>Oops!</h4>
                    <p class="text-danger fw-bold">Data saving failed.</p>
                    <p>Please make sure you fill out all fields. <br> and wait 5 seconds before try submit again. <br><br>
                        If it still can't be done please contact <a href="mailto:admin@localforyou.com">admin@localforyou.com</a> </p>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>