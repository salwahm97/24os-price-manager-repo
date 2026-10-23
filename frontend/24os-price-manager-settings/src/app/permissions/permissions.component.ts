import { Component, OnInit } from '@angular/core';
import {FormBuilder, FormControl, FormGroup} from '@angular/forms';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { PermissionsService } from '../shared/api/permissions.service';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-permissions',
  templateUrl: './permissions.component.html',
  styleUrls: ['./permissions.component.css']
})
export class PermissionsComponent implements OnInit {

  loader!:boolean;
  toast!: toastrPayload;
  screen_loader!:boolean;

  responseData:any;
  responseLang :any;

  permissionsJson: any;

  permissions = new FormGroup({
    author:new FormControl(),
    editor:new FormControl(),
    subscriber:new FormControl(),
    contributor:new FormControl(),
  });

  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private cs: NotificationService,
    private service:PermissionsService
  )
  {
    this.translate.addLangs(['en', 'de']);
    this.translate.setDefaultLang('en');
  }

  ngOnInit(): void {
    this.screen_loader = true;
    this.service.getPermissions().subscribe(response => {
      this.screen_loader = false;
      this.responseData = response;

      this.permissions.controls.author.setValue(this.responseData.author);
      this.permissions.controls.editor.setValue(this.responseData.editor);
      this.permissions.controls.subscriber.setValue(this.responseData.subscriber);
      this.permissions.controls.contributor.setValue(this.responseData.contributor);

    });


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

  applayPermissions(){
    this.loader = true;
    this.screen_loader = true;
    this.permissionsJson = {
      author:this.permissions.controls.author.getRawValue(),
      editor:this.permissions.controls.editor.getRawValue(),
      subscriber:this.permissions.controls.subscriber.getRawValue(),
      contributor:this.permissions.controls.contributor.getRawValue()
    }
    this.service.setPermissions(this.permissionsJson).subscribe(response => {
      this.responseData = response;
      this.screen_loader = false;
      this.loader = false;

      if(this.responseData.message == "permissions updated successfully")
      {
        if(this.responseData.lang == 'en')
        {
          this.toastShow('success', 'Success', 'Permissions Updated Successfully');
        }
        else{
          this.toastShow('success', 'Erfolg', 'Berechtigungen Erfolgreich Aktualisiert');
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
