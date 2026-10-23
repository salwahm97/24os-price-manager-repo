import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class PermissionsService {

  private url = location.href.split("wp-admin")[0] +'wp-json/24os-price-manager/v1/permissions/';
  // private url = 'http://localhost/wordpress/wp-json/24os-price-manager/v1/permissions/';

  constructor(private httpClient: HttpClient) { }

  getPermissions(){
    return this.httpClient.get(this.url);
  }

  setPermissions(inputData:any){
    return this.httpClient.post(this.url, inputData);
  }

}
