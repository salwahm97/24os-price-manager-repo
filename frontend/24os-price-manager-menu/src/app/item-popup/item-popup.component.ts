import { Component, Inject, OnInit } from '@angular/core';
import { NotificationService, toastrPayload } from '../shared/notification.service';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ItemsService } from '../shared/api/items.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { IndividualConfig } from 'ngx-toastr';
import { AllergenService } from '../shared/api/allergen.service';
import { TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../shared/language.service';

@Component({
  selector: 'app-item-popup',
  templateUrl: './item-popup.component.html',
  styleUrls: ['./item-popup.component.css']
})
export class ItemPopupComponent implements OnInit {
  selectedFiles?: FileList;
  selectedFileNames: string[] = [];
  preview !: string;
  preview_name !: string;
  item_image !: boolean;

  responseData:any;
  responseLang: any;

  loader!:boolean;
  screen_loader!:boolean;
  toast!: toastrPayload;
  selection:any;

  category_id !: number;
  index !: number;
  allergenList: any[] = [];
  reactiveItemForm = this.fb.group({
    item_num: [
      '',
      {
        validators: [Validators.required, Validators.min(0)],
      },
    ],
    item_name: ['', [Validators.required]],
    item_desc: ['', [Validators.required]],
    prices: this.fb.array([]),
    allergens:[''],
  });
  constructor(
    private translate : TranslateService,
    private serviceLang:LanguageService,
    private fb: FormBuilder,
    private cs: NotificationService,
    private service:ItemsService,
    private allergenService:AllergenService,
    private itemDialogRef:MatDialogRef<ItemPopupComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ) {
    if(data.action == 'create')
    {
      this.addControl('', '');
    }
    this.allergenService.getAllergens().subscribe(response => {
      this.responseData = response;
      for (let index = 0; index < this.responseData.length; index++) {
        const element = this.responseData[index];
        this.allergenList.push({allergen:element.allergen, allergen_desc:element.allergen_desc});
      }
    });
    this.reactiveItemForm.controls.item_num.setValue(this.data.item_num);
    this.reactiveItemForm.controls.item_name.setValue(this.data.item_name);
    this.reactiveItemForm.controls.item_desc.setValue(this.data.item_desc);
    this.category_id = this.data.category_id;
    this.index = this.data.index;
    this.selection = this.data.item_allergens;
    for (let i = 0; i < this.data.item_prices.length; i++) {
      this.addControl( this.data.item_prices[i].item_price, this.data.item_prices[i].item_size);

    }
    this.item_image = this.data.item_image;
    this.preview = this.data.preview;


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
  get pricesFieldAsFormArray(): any {
    return this.reactiveItemForm.get('prices') as FormArray;
  }

  selectFileImage(event: any): void {

    this.selectedFileNames = [];
    this.selectedFiles = event.target.files;

    if (this.selectedFiles && this.selectedFiles[0]) {
      const numberOfFiles = this.selectedFiles.length;
      for (let i = 0; i < numberOfFiles; i++) {
        const reader = new FileReader();

        reader.onload = (e: any) => {
          this.preview = e.target.result;
        };

        reader.readAsDataURL(this.selectedFiles[i]);

        this.selectedFileNames.push(this.selectedFiles[i].name);
      }
    }
    this.preview_name = this.selectedFileNames[0];
  }

  prices(price:any, size:any): any {
    return this.fb.group({
      item_price: this.fb.control(price, [Validators.required]),
      item_size: this.fb.control(size, [Validators.required]),
    });
  }
  addControl(price:any, size:any): void {
    this.pricesFieldAsFormArray.push(this.prices(price, size));
  }

  remove(i: number): void {
    this.pricesFieldAsFormArray.removeAt(i);
  }


  submitItemDialog(action:string, category_id:number, id:any, lastindex:number, preview:string){
    if(this.reactiveItemForm.valid){
      if(action == 'create' && id == 0 && category_id > 0)
      {
        this.loader = true;
        this.screen_loader = true;
        let searchParams = new URLSearchParams(window.location.search);
        let menu_id = Number(searchParams.get("menu_id"));

        this.service.insertItem(this.reactiveItemForm.getRawValue(), category_id, lastindex + 1, menu_id, preview).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;
          if(this.responseData.message == "item created successfully"){
            if(this.responseData.lang == "en"){

              this.toastShow('success', 'Success', 'Item Created Successfully');
            }
            else
            {
              this.toastShow('success', 'Erfolg', 'Artikel Erfolgreich Erstellt');
            }

            this.itemDialogRef.close();
          }
          else{
            this.toastShow('error', this.responseData.error, this.responseData.message);

          }
        });
      }
      else if(action == 'edit' && id > 0 && category_id > 0)
      {
        this.loader = true;
        this.screen_loader = true;
        let searchParams = new URLSearchParams(window.location.search);
        let menu_id = Number(searchParams.get("menu_id"));

        this.service.updateItem(this.reactiveItemForm.getRawValue(), id, category_id, menu_id, preview).subscribe(response => {
          this.responseData = response;
          this.loader = false;
          this.screen_loader = false;

          if(this.responseData.message == "item updated successfully"){
            if(this.responseData.lang == "en"){

              this.toastShow('success', 'Success', 'Item Updated Successfully');
            }
            else
            {
              this.toastShow('success', 'Erfolg', 'Artikel Erfolgreich Aktualisiert');
            }
            this.itemDialogRef.close();
          }
          else{
            this.toastShow('error', this.responseData.error, this.responseData.message);


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
        timeOut: 3000,
        positionClass: "toast-bottom-right",
      } as IndividualConfig,
    };
    this.cs.showToast(this.toast);
  }
}
