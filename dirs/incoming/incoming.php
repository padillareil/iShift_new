<script src="dirs/incoming/script/incoming.js"></script>
<div class="row" id="picklist-form-content">
    <div class="col-md-2 p-0">
        <div class="sidebar">
            <nav id="menu-incoming" class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" name="menu" menucode="incoming" onclick="loadIncoming()">
                            <i class="nav-icon bi bi-1-square"></i>
                            <p>Create Picklist</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link d-flex align-items-center" name="menu" menucode="picklist" onclick="loadPicklist()">
                            <i class="nav-icon bi bi-2-square"></i>
                            <p class="mb-0">Picklist</p>
                            <span class="badge badge-danger badge-pill ml-auto" id="incoming-picklist-quantity"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link d-flex align-items-center" name="menu" menucode="assignment" onclick="loadAssignment()">
                            <i class="nav-icon bi bi-3-square"></i>
                            <p class="mb-0">Assignment</p>
                            <span class="badge badge-danger badge-pill ml-auto" id="item-assignment"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link d-flex align-items-center" name="menu" menucode="loadingbasket" onclick="loadBasket()">
                            <i class="nav-icon bi bi-4-square"></i>
                            <p class="mb-0">Loading Basket</p>
                            <span class="badge badge-danger badge-pill ml-auto" id="item-loadingbasket"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link d-flex align-items-center" name="menu" menucode="delivery" onclick="loadDispatching()">
                            <i class="nav-icon bi bi-5-square"></i>
                            <p class="mb-0">Dispatching</p>
                            <span class="badge badge-danger badge-pill ml-auto" id="item-deliverydispatch"></span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="col-md-10 container-fluid shadow-sm" style="position: relative;z-index: 10;width: 100%;min-height: calc(100vh - 30px);overflow: visible;box-sizing: border-box;padding: 15px;
         ">
        <section class="content overall-progress" style=" width: 100%; height: 100%; position: relative;">
            <div class="container-fluid overall-progress" id="incoming-content" style=" width: 100%; min-height: 100%; position: relative; z-index: 11; box-sizing: border-box;">
            </div>
        </section>
    </div>
</div>

