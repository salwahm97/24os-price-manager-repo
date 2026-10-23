import { Component, OnInit } from '@angular/core';
import { MenuService } from '../shared/api/menu.service';
import { MatRadioChange } from '@angular/material/radio';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { MatSelectChange } from '@angular/material/select';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.css']
})
export class SettingsComponent implements OnInit {

  toast!: toastrPayload;
  screen_loader!:boolean;
  menu_id : any;
  labelPosition: any;
  selection : any;
  selection_price : string = 'percentage';
  amount !: number;

  responseLang :any;
  responseData :any;

  constructor(
    private service:MenuService,
    private cs: NotificationService,
    private translate : TranslateService,
    private serviceLang:LanguageService
  )
  {
    let paramsString = window.location.search;
    let searchParams = new URLSearchParams(paramsString);
    this.menu_id = Number(searchParams.get("menu_id"));
    if(Number.isInteger(this.menu_id) && this.menu_id > 0){
      this.getStyleMenu(this.menu_id);
      this.getOptionImageMenu(this.menu_id);

    }

    this.translate.addLangs(['en', 'de']);
    this.translate.setDefaultLang('en');
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

  getStyleMenu(menu_id:any){
    this.service.getStyleMenu(menu_id).subscribe(response => {
      this.labelPosition = response;
    });
  }

  getOptionImageMenu(menu_id:any){
    this.service.getOptionImageMenu(menu_id).subscribe(response => {
      this.selection = response;
    });
  }

  setOptionImageMenu(menu_id:any, event: MatSelectChange){
    let option = event.value;
    this.screen_loader = true;
    this.service.setOptionImageMenu(menu_id, option).subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;
      if(this.responseData.message == 'The possibility of inserting pictures updated successfully')
      {
        if(this.responseData.lang == 'en')
        {
          this.toastShow('success', 'Success', 'The possibility of inserting pictures updated successfully');
        }
        else{
          this.toastShow('success', 'Erfolg', 'Die Möglichkeit, Bilder einzufügen, wurde erfolgreich aktualisiert');
        }
      }
    });
  }

  setOptionPriceItemsMenu(event: MatSelectChange){
    this.selection_price = event.value;

  }

  incPrice(menu_id:any){
    if(this.amount != undefined)
    {
      this.screen_loader = true;
      this.service.incPrice(menu_id, this.amount, this.selection_price).subscribe(response => {
        this.screen_loader = false;
        this.responseData = response;
        if(this.responseData.message == 'The prices of all items in this menu Increased successfully')
        {
          window.location.reload();
          if(this.responseData.lang == 'en')
          {
            this.toastShow('success', 'Success', 'The prices of all items in this menu Increased successfully');
          }
          else{
            this.toastShow('success', 'Erfolg', 'Die Preise aller Artikel in diesem Menü wurden erfolgreich erhöht');
          }
        }
      });
    }
  }

  radioChange(menu_id:any, event: MatRadioChange) {
    let style = event.value;
    this.screen_loader = true;
    this.service.setStyleMenu(menu_id, style).subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;
      if(this.responseData.message == 'menu style updated successfully')
      {
        if(this.responseData.lang == 'en')
        {
          this.toastShow('success', 'Success', 'Menu Style Updated Successfully');
        }
        else{
          this.toastShow('success', 'Erfolg', 'Menüstil erfolgreich aktualisiert');
        }
      }
    });
  }

  renumber(menu_id:any){
    this.screen_loader = true;
    this.service.renumberMenu(menu_id).subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;
      if(this.responseData.message == 'menu renumbered successfully')
      {
        if(this.responseData.lang == 'en')
        {
          this.toastShow('success', 'Success', 'Menu Renumbered Successfully');
        }
        else{
          this.toastShow('success', 'Erfolg', 'Menü erfolgreich neu nummeriert');
        }
      }
    });
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
