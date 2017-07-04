//
//  ViewController.m
//  You're DONE
//
//  Created by Leo Lorenz on 1/22/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import "ViewController.h"
#import "ParentTableCell.h"
#import "BaseroutAPI.h"
#import "TimerViewController.h"
#import "AppDelegate.h"
#import "ReTimerViewController.h"
#import "UserModel.h"
#import "SVProgressHUD.h"

@interface ViewController ()
{
    AppDelegate *delegate;
    
}
@property (weak, nonatomic) IBOutlet UIButton *addButton;
@property (weak, nonatomic) IBOutlet UIButton *delButton;
@property (weak, nonatomic) IBOutlet UIView *alertView;
@property (weak, nonatomic) IBOutlet UIButton *alertOKbutton;
@property (weak, nonatomic) IBOutlet UIButton *alertCancelButton;
@property (weak, nonatomic) IBOutlet UITextField *nameTextField;
@property (weak, nonatomic) IBOutlet UITextField *passcodeTextField;
@property (weak, nonatomic) IBOutlet UITableView *tableView;
@property (weak, nonatomic) IBOutlet UILabel *addbuttonLabel;
@property (weak, nonatomic) IBOutlet UIView *removeAlertView;
@property (weak, nonatomic) IBOutlet UIView *addAlertView;
@property (weak, nonatomic) IBOutlet UIButton *removeOKButton;
@property (weak, nonatomic) IBOutlet UIButton *removeCancelButton;

@end

@implementation ViewController
{
    NSMutableArray *nameArray;
    NSMutableArray *passcodeArray;
    int selected_Index;
}

- (void)viewDidLoad {
    [super viewDidLoad];
    
    delegate = [[UIApplication sharedApplication] delegate];
    [self.navigationController.navigationBar setHidden:NO];

    [self.navigationItem setHidesBackButton:YES];
    
    selected_Index = -1;            //initialize selected cell index
    nameArray = [[NSMutableArray alloc] init];
    passcodeArray = [[NSMutableArray alloc] init];
    
    //I have already set this code for Navigation Bar in Appdelegate
    NSShadow *shadow = [[NSShadow alloc] init];
    shadow.shadowColor = [UIColor colorWithRed:0.0 green:0.0 blue:0.0 alpha:0.8];
    shadow.shadowOffset = CGSizeMake(0, 1);
    [[UINavigationBar appearance] setTitleTextAttributes: [NSDictionary dictionaryWithObjectsAndKeys:
                                                           [UIColor colorWithRed:245.0/255.0 green:245.0/255.0 blue:245.0/255.0 alpha:1.0], NSForegroundColorAttributeName,
                                                           shadow, NSShadowAttributeName,
                                                           [UIFont fontWithName:@"HelveticaNeue-CondensedBlack" size:21.0], NSFontAttributeName, nil]];
    [[UINavigationBar appearance] setBarTintColor:[UIColor yellowColor]];
    
    //AlertView that enter a name and passcode is hidden.
    [self.alertView setHidden:true];
    
    self.alertOKbutton.layer.borderWidth =0;
    self.alertOKbutton.layer.cornerRadius = 7.0;
    self.alertOKbutton.layer.masksToBounds = YES;
    self.alertCancelButton.layer.borderWidth = 0;
    self.alertCancelButton.layer.cornerRadius = 7.0;
    self.alertCancelButton.layer.masksToBounds = YES;
    self.removeOKButton.layer.borderWidth =0;
    self.removeOKButton.layer.cornerRadius = 7.0;
    self.removeOKButton.layer.masksToBounds = YES;
    self.removeCancelButton.layer.borderWidth = 0;
    self.removeCancelButton.layer.cornerRadius = 7.0;
    self.removeCancelButton.layer.masksToBounds = YES;
    self.addAlertView.layer.borderWidth = 0;
    self.addAlertView.layer.cornerRadius = 7.0;
    self.addAlertView.layer.masksToBounds = YES;
    self.removeAlertView.layer.borderWidth = 0;
    self.removeAlertView.layer.cornerRadius = 7.0;
    self.removeAlertView.layer.masksToBounds = YES;
    
    
    //Show the Done on the keyboard begin
    UIToolbar* keyboardDoneButtonView = [[UIToolbar alloc] init];
    [keyboardDoneButtonView sizeToFit];
    UIBarButtonItem* doneButton = [[UIBarButtonItem alloc] initWithTitle:@"Done"
                                                                   style:UIBarButtonItemStylePlain target:self
                                                                  action:@selector(doneClicked:)];
    [keyboardDoneButtonView setItems:[NSArray arrayWithObjects:doneButton, nil]];
    
    _nameTextField.inputAccessoryView = keyboardDoneButtonView;
    _passcodeTextField.inputAccessoryView = keyboardDoneButtonView;
    
    //custom back button in navigation Bar
    UIBarButtonItem *myBackButton = [[UIBarButtonItem alloc] initWithTitle:@"Back"
                                                                     style:UIBarButtonItemStyleDone target:nil action:nil];
    self.navigationItem.backBarButtonItem = myBackButton;
    self.navigationController.navigationBar.tintColor = [UIColor yellowColor];

}

- (void)viewDidAppear:(BOOL)animated
{
    [super viewWillAppear:animated];
    
    selected_Index = -1;
    
    [self.tableView reloadData];
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (IBAction)okButtonclicked:(id)sender {
    
    if([_nameTextField.text length] <= 0) {
        
        [[BaseroutAPI sharedInstance] MessageBox:@"Invalid Input" Message:@"Please enter valid name"];
    }else
    if([_passcodeTextField.text length] <= 0)
    {
        [[BaseroutAPI sharedInstance] MessageBox:@"Invalid Input" Message:@"Please enter valid passcode"];
    }else
    
    if ([nameArray count] > 4) {
        [[BaseroutAPI sharedInstance] MessageBox:@"Invalid Input" Message:@"You can set the phone of up to 5 children"];
        [self.alertView setHidden:true];
    }else
    if([self.nameTextField.text length] > 0 && [self.passcodeTextField.text length] > 0 && [nameArray count] <= 4)
    {
        
        //Api Connection
        apimanager = [[Apimanager alloc] init];
        [SVProgressHUD showWithStatus:@"Loging in..."];
        NSString *api_name;
        NSDictionary *params;
        api_name = @"/get/child";
        params = @{@"name":self.nameTextField.text,@"passcode":self.passcodeTextField.text};

        [apimanager callAPI:api_name withParams:params success:^(NSData *data) {
            
            dispatch_sync(dispatch_get_main_queue(), ^{
                [SVProgressHUD dismiss];
                NSError *errorJson = nil;
                NSDictionary *dataDict = [NSJSONSerialization JSONObjectWithData:data options:kNilOptions error:&errorJson];
                
                if ([((NSDictionary *)dataDict)[@"status"] isEqualToString:@"OK"]){
                    NSLog(@"%@", dataDict);
                    
                            NSArray *tempId = [dataDict objectForKey:@"data"];
                            NSDictionary *temp_id_dict = [tempId firstObject];
                            NSString *temp_id = [temp_id_dict objectForKey:@"id"];
                    
                            [nameArray addObject:self.nameTextField.text];
                            [passcodeArray addObject:self.passcodeTextField.text];
                    
                            UserModel * userModel = [[UserModel alloc] init];
                            userModel.username = self.nameTextField.text;
                            userModel.userpasscode = self.passcodeTextField.text;
                            userModel.child_id = temp_id;
                            [delegate.arrayUsers addObject:userModel];
                            
                            [self.tableView reloadData];
                            [self.alertView setHidden:true];
                    
                  
                }else if([((NSDictionary *)dataDict)[@"status"] isEqualToString:@"failed"])
                {
                    [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Can not find a user in server.Had you registered a user on child phone?"];
  
                }
            });
        }
                      error:^(NSError *error){
                          
                          [SVProgressHUD dismiss];
                          [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Please check network status out!"];
                          
                      }];


    }
    
    [self.addbuttonLabel setHidden:YES];
   
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
    return [nameArray count];
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath
{
    
    ParentTableCell *cell = [tableView dequeueReusableCellWithIdentifier:@"parentTableCell"];
       cell.nameLabel.text = [nameArray objectAtIndex:indexPath.row];
    
    //set the button title programatically
    [cell.buttonImage setTitle:[nameArray objectAtIndex:indexPath.row] forState:UIControlStateNormal];
    cell.buttonImage.tag = indexPath.row;
    
    //set up the color in tableView Cell
    UIView *selectedBgView = [[UIView alloc] initWithFrame:cell.frame];
    [selectedBgView setBackgroundColor:[UIColor cyanColor]];
    [cell setSelectedBackgroundView:selectedBgView];
    selectedBgView.layer.borderWidth =0;
    selectedBgView.layer.cornerRadius = 8.0;
    selectedBgView.layer.masksToBounds = YES;
    
//    //change the name and button color when selected the cell
//    UIImage *btnImage = [UIImage imageNamed:@"changButton.png"];
//    [cell.buttonImage setImage:btnImage forState:UIControlStateHighlighted];
    
    //set up verify image when password is correct
    UserModel * usermodel = [delegate.arrayUsers objectAtIndex:indexPath.row];
    int status = usermodel.status;
    
    switch (status) {
        case 0:
            [cell.verifyImage setImage:nil];
            [cell.buttonImage setImage:[UIImage imageNamed:@"button image.png"] forState:UIControlStateNormal];
            [cell.nameLabel setTextColor:[UIColor colorWithRed:0 green:36/255.0 blue:84/255.0 alpha:1]];
            break;
        case 1:
            [cell.verifyImage setImage:[UIImage imageNamed:@"verify.png"]];
            [cell.buttonImage setImage:[UIImage imageNamed:@"namebutton.png"] forState:UIControlStateNormal];
            [cell.nameLabel setTextColor:[UIColor colorWithRed:155/255.0 green:25/255.0 blue:23/255.0 alpha:1]];
            break;
        case 2:
            [cell.verifyImage setImage:nil];
            [cell.buttonImage setImage:[UIImage imageNamed:@"button image.png"] forState:UIControlStateNormal];
            [cell.nameLabel setTextColor:[UIColor colorWithRed:0 green:36/255.0 blue:84/255.0 alpha:1]];
            break;
        default:
            break;
    }

    return cell;
}

-(void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath{
    selected_Index = (int)indexPath.row;
}



- (IBAction)addButtonClicked:(id)sender {
    
    [self.removeAlertView setHidden:true];
    [self.alertView setHidden:false];
    [self.addAlertView setHidden:NO];
    
    //Automatically display the keyboard
    [self.nameTextField becomeFirstResponder];
    
    self.nameTextField.text = @"";
    self.passcodeTextField.text = @"";
    
    selected_Index = -1;
    
}
- (IBAction)removeButtonClicked:(id)sender {
    if([nameArray count] == 0)
       [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"There are no phones to choose from."];
    if([nameArray count] >  0)
    {   if(selected_Index == -1)
           [[BaseroutAPI sharedInstance] MessageBox:@"Notice" Message:@"Please select a phone."];
    else{
            [self.alertView setHidden:NO];
            [self.addAlertView setHidden:YES];
            [self.removeAlertView setHidden:NO];
        }
    }
}

- (IBAction)cancelButtonClicked:(id)sender {
    
    [self.alertView setHidden:true];
}


//when press keyboard done button
- (IBAction)doneClicked:(id)sender
{
    [self.view endEditing:YES];
    
}
- (IBAction)tableButtonClicked:(id)sender {
    
    selected_Index = (int)[sender tag];
    delegate.selectedIndex = selected_Index;
    UserModel * usermodel = [delegate.arrayUsers objectAtIndex:selected_Index];
    int status = usermodel.status;
    selected_Index = -1;
    
    if (status == 1) {
        ReTimerViewController * vc = (ReTimerViewController*)[[UIStoryboard storyboardWithName:@"Main" bundle:nil] instantiateViewControllerWithIdentifier:@"ReTimerView"];
         vc.nameTimerLabel = [sender currentTitle];
        for(int i = 0; i < [nameArray count]; i++)
        {
            if(nameArray[i] == [sender currentTitle])   {
                vc.passcodeTimerLabel = passcodeArray[i];
            }
        }

        [self.navigationController pushViewController:vc animated:YES];

    }
    else if(status == 0){
        
        TimerViewController * vc = (TimerViewController*)[[UIStoryboard storyboardWithName:@"Main" bundle:nil] instantiateViewControllerWithIdentifier:@"timerView"];
        vc.nameTimerLabel = [sender currentTitle];
        
        delegate.selectedIndex = selected_Index;
        for(int i = 0; i < [nameArray count]; i++)
        {
            if(nameArray[i] == [sender currentTitle])   {
                vc.passcodeTimerLabel = passcodeArray[i];
                delegate.selectedIndex = i;
//                delegate.flag = NO;
            }
        }
        
        [self.navigationController pushViewController:vc animated:YES];
        
        
    }
    
}
- (IBAction)removeOKButton:(id)sender {
    
    if(selected_Index > -1)
    {
        [nameArray removeObjectAtIndex:selected_Index];
        [delegate.arrayUsers removeObjectAtIndex:selected_Index];
        selected_Index = -1;
    }
    [self.alertView setHidden:YES];
    [self.tableView reloadData];
}
- (IBAction)removeCancelButton:(id)sender {
    
    [self.removeAlertView setHidden:YES];
    [self.alertView setHidden:YES];
}

@end
