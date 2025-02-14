<div id="modaldelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" style="color: black" id="exampleModalLabel">Eliminar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Elemento: <samp class="txtblack">@{{delobj}}</samp> </p>
                <p>Cuidado! Esta acción es irreversible. Desea proceder?</p>
            </div>
            <div class="modal-footer">
                <button @click="delitem()" class="btn btn-danger btn-sm">SI</button>
                <a href="#" data-dismiss="modal" class="btn btn-default  btn-sm">No</a>
            </div>
        </div>
    </div>
</div>
