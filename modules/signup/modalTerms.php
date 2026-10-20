<!-- Modal -->
<div class="modal fade" id="modalTerms" tabindex="-1" aria-labelledby="modalTermsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTermsLabel">TERMS AND CONDITIONS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php include("TermsAndCondition.html"); ?>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto" id="termsScrollHint">Please scroll to the end to agree.</small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-secondary" id="btnTermsAgree" disabled>I Agree</button>
            </div>
        </div>
    </div>
</div>