import { Component, Inject, OnInit } from '@angular/core';
import { AllergenService } from '../shared/api/allergen.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { IndividualConfig } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-allergen-popup',
  templateUrl: './allergen-popup.component.html',
  styleUrls: ['./allergen-popup.component.css']
})
export class AllergenPopupComponent implements OnInit {

  responseData:any;
  responseLang :any;

  loader!:boolean;
  screen_loader!:boolean;
  toast!: toastrPayload;

  reactiveAllergenForm = new FormGroup({
    allergen:new FormControl('', Validators.required),
    allergen_desc : new FormControl('', Validators.required)
  });

  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private cs: NotificationService,
    private service:AllergenService,
    private allergenDialogRef:MatDialogRef<AllergenPopupComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ) {
    this.reactiveAllergenForm.controls.allergen.setValue(this.data.allergen);
    this.reactiveAllergenForm.controls.allergen_desc.setValue(this.data.allergen_desc);

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

  submitAllergenDialog(action:string, id:any){
    if(this.reactiveAllergenForm.valid){
      if(action == 'create' && id == 0)
      {
        this.loader = true;
        this.screen_loader = true;
        this.service.insertAllergen(this.reactiveAllergenForm.getRawValue()).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;
          if(this.responseData.message == "allergen created successfully"){
            if(this.responseData.lang == 'en')
            {
              this.toastShow('success', 'Success', 'Allergen Created Successfully');
            }
            else{
              this.toastShow('success', 'Erfolg', 'Allergen Erfolgreich Erstellt');
            }

            this.allergenDialogRef.close();
          }

        });
      }
      else if(action == 'edit' && id > 0)
      {
        this.loader = true;
        this.screen_loader = true;
        this.service.updateAllergen(this.reactiveAllergenForm.getRawValue(), id).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;
          if(this.responseData.message == "allergen updated successfully"){
            if(this.responseData.lang == 'en')
            {
              this.toastShow('success', 'Success', 'Allergen Updated Successfully');
            }
            else{
              this.toastShow('success', 'Erfolg', 'Allergen Erfolgreich Aktualisiert');
            }

            this.allergenDialogRef.close();
          }

        });
      }
    }
    else{
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
