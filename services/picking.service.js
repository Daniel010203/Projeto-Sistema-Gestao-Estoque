window.PickingService=Object.freeze({create(p){return BackendService.postMain('create_picking',p);},complete(p){return BackendService.postMain('complete_picking',p);}});
