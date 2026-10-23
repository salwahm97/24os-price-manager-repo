import { Component, Inject, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { MenuService } from '../shared/api/menu.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-menu-popup',
  templateUrl: './menu-popup.component.html',
  styleUrls: ['./menu-popup.component.css']
})
export class MenuPopupComponent implements OnInit {

  toast!: toastrPayload;
  loader!:boolean;
  screen_loader!:boolean;

  priceManagerPostCategoryList: string[] = [];
  selection: any;
  responseData:any;
  responsepriceManagerPostCategoriesData: any;

  reactiveMenuForm = new FormGroup({
    menu:new FormControl('', Validators.required),
    priceManagerPostCategories : new FormControl()
  });
  responseLang: any;

  constructor(
    private translate : TranslateService,
    private cs: NotificationService,
    private service:MenuService,
    private serviceLang:LanguageService,
    private menuDialogRef:MatDialogRef<MenuPopupComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ){
    this.service.getPriceManagerPostCategories().subscribe(response => {
      this.responsepriceManagerPostCategoriesData = response;
      for (let index = 0; index < this.responsepriceManagerPostCategoriesData.length; index++) {
        const element = this.responsepriceManagerPostCategoriesData[index];
        this.priceManagerPostCategoryList.push(element.name);
      }
    });

    this.selection = this.data.menuType;
    this.reactiveMenuForm.controls.menu.setValue(this.data.menu);


    translate.addLangs(['en', 'de']);
    translate.setDefaultLang('en');

  }

  ngOnInit(): void {
    this.serviceLang.getLang().subscribe(response => {
      this.responseLang = response;
      this.translate.use(this.responseLang);
    });

    this.serviceLang.requiredRefresh.subscribe(r=>{
      this.serviceLang.getLang().subscribe(response => {
        this.responseLang = response;
        this.translate.use(this.responseLang);
      });
    });
  }

  submitMenuDialog(action:string, id:any){
    if(this.reactiveMenuForm.valid){
      if(action == 'create' && id == 0)
      {
        this.loader = true;
        this.screen_loader = true;
        this.service.insertMenu(this.reactiveMenuForm.getRawValue()).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;
          if(this.responseData.message == "menu created successfully"){
            if(this.responseData.lang == "en"){
              this.toastShow('success', 'Success', 'Menu Created Successfully');
            }
            else{
              this.toastShow('success', 'Erfolg', 'Menü Erfolgreich Erstellt');
            }
            this.menuDialogRef.close();
          }

        });
      }
      else if(action == 'edit' && id > 0)
      {
        this.loader = true;
        this.screen_loader = true;
        this.service.updateMenu(this.reactiveMenuForm.getRawValue(), id).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;
          if(this.responseData.message == "menu updated successfully"){
            if(this.responseData.lang == "en"){
              this.toastShow('success', 'Success', 'Menu Updated Successfully');
            }
            else{
              this.toastShow('success', 'Erfolg', 'Menü Erfolgreich Aktualisiert');
            }
            this.menuDialogRef.close();
          }
        });
      }
    }

  }

  toastShow(type: string, title: string, message:string){
    this.toast = {
      message: message,
      title: title,
      type: type,
      ic:{
        timeOut: 2500,
        positionClass: "toast-bottom-right",
      } as IndividualConfig,
    };
    this.cs.showToast(this.toast);
  }

}
