import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { menuModel } from '../../model/menu-model';
import { Observable, Subject, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class MenuService {

  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/menu/';
  private trash_url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/menu-recovery/';
  private price_manager_post_categories_url = location.href.split("wp-admin")[0] +'wp-json/wp/v2/24os_price_manager_categories/';
  private active_url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/active-menu/';

  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/menu/';
  // private trash_url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/menu-recovery/';
  // private price_manager_post_categories_url = 'http://localhost/wordpress/wp-json/wp/v2/24os_price_manager_categories/';
  // private active_url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/active-menu/';

  private _refreshRequired = new Subject<void>();
  get requiredRefresh(){
    return this._refreshRequired;
  }

  constructor(private httpClient: HttpClient) { }

  getMenus():Observable<menuModel[]>{
    return this.httpClient.get<menuModel[]>(this.url);
  }

  getMenuById(id:any){
    return this.httpClient.get<menuModel[]>(this.url + id);
  }

  insertMenu(inputData:any){
    return this.httpClient.post(this.url, inputData).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  updateMenu(inputData:any, id: any){
    return this.httpClient.patch(this.url + id, inputData).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  trashMenu(id: any){
    return this.httpClient.post(this.trash_url + id, {recovery:'trash'}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }

  getPriceManagerPostCategories(){
    return this.httpClient.get(this.price_manager_post_categories_url);

  }

  activeMenu(active:boolean, id:any){
    return this.httpClient.post(this.active_url, {active:active, id:id}).pipe(
      tap(()=>{
        this.requiredRefresh.next();
      })
    );
  }
}
