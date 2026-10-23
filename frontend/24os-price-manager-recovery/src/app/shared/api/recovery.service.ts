import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, Subject, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RecoveryService {

  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/menu-recovery/';
  private delete_url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/menu/';

  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/menu-recovery/';
  // private delete_url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/menu/';

  private _refreshRequired = new Subject<void>();
  get requiredRefresh(){
    return this._refreshRequired;
  }

  constructor(private httpClient: HttpClient) { }

  getTrashMenus(){
    return this.httpClient.get(this.url);
  }

  deleteMenu(id: any){
    return this.httpClient.delete(this.delete_url + id).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  restoreMenu(id:any){
    return this.httpClient.post(this.url + id, {recovery:'restore'}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

}
